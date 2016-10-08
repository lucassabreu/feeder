<?php

namespace Core\Service\Util;

use Core\Service\Service;
use InvalidArgumentException;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SendmailTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Message as MessagePart;
use Zend\Mime\Mime;
use Zend\Mime\Part;

/**
 * Implements basic functions to send e-mail with ZF2
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class MailUtilService extends Service {

    /**
     * Send a e-mail by the params.
     * 
     * @param string $title Message's title
     * @param string $body Content will be send
     * @param string $mimeType Mime-Type of <code>$body</code>
     * @param array $to Array with format email => names
     * @param array $cc (optional) Array with format email => names
     * @param array $cco (optional) Array with format email => names
     */
    public function sendEmail($title, $body, $mimeType, $to, $cc = array(), $cco = array()) {

        $config = $this->getService('Config');
        /* @var $config array */

        if (!isset($config['email_sending']) || !isset($config['email_sending']['from']) || !isset($config['email_sending']['transport_options'])) {
            throw new InvalidArgumentException("Email sending params not exists in config.");
        }

        $part = new Part($body);
        $part->type = $mimeType;
        $part->disposition = Mime::DISPOSITION_INLINE;
        $part->encoding = Mime::ENCODING_QUOTEDPRINTABLE;
        $part->charset = 'utf-8';

        $body = new MessagePart();
        $body->addPart($part);

        $message = new Message();
        $message->setBody($body);

        foreach ($to as $email => $name) {
            $message->addTo($email, $name);
        }

        if (isset($config['email_sending']['fromName']))
            $message->addFrom($config['email_sending']['from'], $config['email_sending']['fromName']);
        else
            $message->addFrom($config['email_sending']['from']);

        $message->setSubject($title);

        $transport = new SendmailTransport();
        $options = new SmtpOptions($config['email_sending']['transport_options']);
        $transport->setOptions($options);
        $transport->send($message);
    }

}

?>
