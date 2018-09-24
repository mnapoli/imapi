<?php
declare(strict_types = 1);

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

    /**
     * Returns the name of the mailbox this email exists in.
     *
     * @return string
     */
    public function getMailbox() : string
    {
        return $this->mailbox;
    }

    /**
     * Returns the email's subject.
     * 
     * @return string
     */
    public function getSubject() : string
    {
        return $this->message->getHeaderValue('Subject');
    }

    /**
     * Returns raw HTML content (if any).
     *
     * @return string
     */
    public function getHtmlContent() : string
    {
        $content = $this->message->getHtmlContent();
        if ($content === null) {
            // strict_types
            return '';
        }
        return $content;
    }

    /**
     * Returns the HTML content of the email, sanitized using HTMLPurifier.
     *
     * @return string
     */
    public function getSanitizedHtmlContent() : string
    {
        return trim($this->htmlFilter->purify($this->getHtmlContent()));
    }

    /**
     * Returns the email's text content (if any).
     *
     * @return string
     */
    public function getTextContent() : string
    {
        $text = $this->message->getTextContent();
        if ($text === null) {
            // strict_types
            return '';
        }
        return $text;
    }

    /**
     * Returns the email's date, or null if not set.
     *
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
     * Returns addresses of the email's From header.
     *
     * @return \ZBateson\MailMimeParser\Header\Part\AddressPart[]|null
     */
    public function getFrom()
    {
        $from = $this->message->getHeader('From');
        if ($from !== null) {
            return $from->getAddresses();
        }
        return null;
    }

    /**
     * Returns addresses in the email's To header.
     *
     * @return \ZBateson\MailMimeParser\Header\Part\AddressPart[]|null
     */
    public function getTo()
    {
        $from = $this->message->getHeader('To');
        if ($from !== null) {
            return $from->getAddresses();
        }
        return null;
    }

    /**
     * Returns true if the email has been marked as read.
     *
     * @return bool
     */
    public function isRead() : bool
    {
        return $this->read;
    }

    /**
     * Returns the Message object, useful to get additional header values,
     * etc...
     *
     * @return Message
     */
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
