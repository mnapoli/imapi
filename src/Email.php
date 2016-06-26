<?php
declare(strict_types = 1);

namespace Imapi;

use DateTime;

/**
 * Email.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Email
{
    /**
     * @var string
     */
    private $uid;

    /**
     * @var string|null
     */
    private $messageId;

    /**
     * @var string
     */
    private $mailbox;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $htmlContent;

    /**
     * @var string
     */
    private $textContent;

    /**
     * @var DateTime|null
     */
    private $date;

    /**
     * @var EmailAddress[]
     */
    private $from = [];

    /**
     * @var EmailAddress[]
     */
    private $to = [];

    /**
     * @var bool
     */
    private $read = false;

    /**
     * @var string|null
     */
    private $inReplyTo;

    /**
     * @param string|null $messageId
     * @param EmailAddress[] $from
     * @param EmailAddress[] $to
     * @param string|null $inReplyTo
     */
    public function __construct(
        string $uid,
        $messageId,
        string $mailbox,
        string $subject,
        string $htmlContent,
        string $textContent,
        array $from = [],
        array $to = [],
        $inReplyTo
    ) {
        $this->uid = $uid;
        $this->messageId = $messageId;
        $this->mailbox = $mailbox;
        $this->subject = $subject;
        $this->htmlContent = $htmlContent;
        $this->textContent = $textContent;
        $this->from = $from;
        $this->to = $to;
        $this->inReplyTo = $inReplyTo;
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
        return $this->messageId;
    }

    public function getMailbox() : string
    {
        return $this->mailbox;
    }

    public function getSubject() : string
    {
        return $this->subject;
    }

    public function getHtmlContent() : string
    {
        return $this->htmlContent;
    }

    public function getTextContent() : string
    {
        return $this->textContent;
    }

    /**
     * @return DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }

    public function setDate(DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return EmailAddress[]
     */
    public function getFrom() : array
    {
        return $this->from;
    }

    /**
     * @return EmailAddress[]
     */
    public function getTo() : array
    {
        return $this->to;
    }

    public function setRead(bool $read)
    {
        $this->read = $read;
    }

    public function isRead() : bool
    {
        return $this->read;
    }

    /**
     * Message ID of the email this email is a reply to.
     *
     * @return string|null
     */
    public function getInReplyTo()
    {
        return $this->inReplyTo;
    }
}
