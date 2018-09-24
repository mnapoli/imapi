<?php

namespace Imapi;

use DateTime;
use HTMLPurifier;
use ZBateson\MailMimeParser\Message;

/**
 * Email.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Email
{
    /**
     * @var HTMLPurifier
     */
    private $htmlFilter;

    /**
     * @var string
     */
    private $uid;
    
    /**
     * @var string
     */
    private $mailbox;

    /**
     * @var Message
     */
    private $message;

    /**
     * @var bool
     */
    private $read;

    /**
     * @param HTMLPurifier $htmlFilter
     * @param string $uid
     * @param string $mailbox
     * @param bool $read
     * @param Message $message
     */
    public function __construct(
        HTMLPurifier $htmlFilter,
        string $uid,
        string $mailbox,
        bool $read,
        Message $message
    ) {
        $this->htmlFilter = $htmlFilter;
        $this->uid = $uid;
        $this->mailbox = $mailbox;
        $this->read = $read;
        $this->message = $message;
    }

    /**
     * Returns the UID of the IMAP message.
     *
     * This ID is set by the IMAP server.
     *
     * UID of the email may be not unique on the server (2 messages in different folders may have same UID).
     *
     * @see http://www.limilabs.com/blog/unique-id-in-imap-protocol
     */
    public function getUid() : string
    {
        return $this->uid;
    }

    /**
     * Returns the "Message-ID" header.
     *
     * @see https://en.wikipedia.org/wiki/Message-ID
     * @return string|null
     */
    public function getMessageId()
    {
        return $this->parseMessageId(
            $this->message->getHeaderValue('Message-ID')
        );
    }

    /**
     * Message ID of the email this email is a reply to.
     *
     * @return string|null
     */
    public function getInReplyTo()
    {
        return $this->parseMessageId(
            $this->message->getHeaderValue('In-Reply-To')
        );
    }

    public function getMailbox() : string
    {
        return $this->mailbox;
    }

    public function getSubject() : string
    {
        return $this->message->getHeaderValue('Subject');
    }

    public function getHtmlContent() : string
    {
        return $this->message->getHtmlContent();
    }

    public function getSanitizedHtmlContent() : string
    {
        return trim($this->htmlFilter->purify($this->getHtmlContent()));
    }

    public function getTextContent() : string
    {
        return $this->message->getTextContent();
    }

    /**
     * @return DateTime|null
     */
    public function getDate()
    {
        $date = $this->message->getHeader('Date');
        if ($date !== null) {
            return $date->getDateTime();
        }
        return null;
    }

    /**
     * @return \ZBateson\MailMimeParser\Header\Part\AddressPart[]|null
     */
    public function getFrom()
    {
        $from = $this->message->getHeader('From');
        if ($from !== null) {
            return $from->getAddresses();
        }
    }

    /**
     * @return \ZBateson\MailMimeParser\Header\Part\AddressPart[]|null
     */
    public function getTo()
    {
        $from = $this->message->getHeader('To');
        if ($from !== null) {
            return $from->getAddresses();
        }
    }

    public function isRead() : bool
    {
        return $this->read;
    }

    public function getMessage() : Message
    {
        return $this->message;
    }

    /**
     * @param string|null $messageId
     * @return string|null
     */
    private function parseMessageId($messageId)
    {
        if (!$messageId) {
            return null;
        }
        $result = preg_match('/<([^>]*)>/', $messageId, $matches);
        if ($result === false || $result === 0) {
            return null;
        }
        return $matches[1];
    }
}
