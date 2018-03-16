<?php

namespace aface\mailgun;

use Mailgun\Mailgun;
use yii\base\InvalidConfigException;
use yii\mail\BaseMailer;
use Yii;

/**
 * Mailer implements a mailer based on Mailgun.
 * To use Mailer, you should configure it in the application configuration like the following,
 * To send an email, you may use the following code:
 *
 * ~~~
 * Yii::$app->mailer->compose('contact/html', ['contactForm' => $form])
 *     ->setFrom('from@domain.com')
 *     ->setTo($form->email)
 *     ->setSubject($form->subject)
 *     ->send();
 * ~~~
 */
class Mailer extends BaseMailer
{
    /**
     * @var string message default class name.
     */
    public $messageClass = 'aface\mailgun\Message';

    /**
     * @var string Mailgun API credentials.
     * @see https://app.mailgun.com/app/account/security
     */
    public $key;

    /**
     * @var string Mailgun API Email Validation Key.
     * @see https://app.mailgun.com/app/account/security
     */
    public $emailValidKey;

    /** @var string Mailgun API Email Validation URL */
    public $emailValidUrl = 'https://api.mailgun.net/v3/address/validate';

    /**
     * @var string Mailgun domain.
     */
    public $domain;

    /**
     * @var Mailgun Mailgun instance.
     */
    private $_mailgun;

    /**
     * @return Mailgun Mailgun instance.
     */
    public function getMailgun()
    {
        if (!is_object($this->_mailgun)) {
            $this->_mailgun = $this->createMailgun();
        }

        return $this->_mailgun;
    }

    /**
     * @param \yii\mail\MessageInterface $message
     * @return bool
     */
    protected function sendMessage($message)
    {
        $result = $this->getMailgun()->sendMessage(
            $this->domain,
            $message->getMessageBuilder()->getMessage(),
            $message->getMessageBuilder()->getFiles()
        );

        Yii::info('Sending email', print_r($result, true));

        if ($result->http_response_code === 200) {
            return true;
        }

        return false;
    }

    /**
     * Creates Mailgun instance.
     * @return Mailgun Mailgun instance.
     * @throws InvalidConfigException if required params are not set.
     */
    protected function createMailgun()
    {
        if (!$this->key) {
            throw new InvalidConfigException('Mailer::key must be set.');
        }
        if (!$this->domain) {
            throw new InvalidConfigException('Mailer::domain must be set.');
        }
        return Mailgun::create($this->key);
    }

    /**
     * Use curl to send the validation request to Mailgun
     * @param string $email
     * @param boolean $returnBool
     * @return boolean|\stdClass
     * @throws \Exception
     */
    public function emailValidate($email, $returnBool = true)
    {
        $mgClient = Mailgun::create($this->emailValidKey);
        $result = $mgClient->get('address/validate', [
            'address' => $email,
            'mailbox_verification' => true
        ]);

        if ($returnBool !== true) {
            return $result;
        }

        if ($result->http_response_code === 200) {
            if ($result->http_response_body->mailbox_verification == 'unknown') {
                return $result->http_response_body->is_valid;
            }
            return filter_var($result->http_response_body->mailbox_verification, FILTER_VALIDATE_BOOLEAN);
        }

        return false;
    }
}