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
    private $id;

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
     * @param string         $id
     * @param string         $mailbox
     * @param string         $subject
     * @param string         $htmlContent
     * @param string         $textContent
     * @param EmailAddress[] $from
     * @param EmailAddress[] $to
     */
    public function __construct(
        string $id,
        string $mailbox,
        string $subject,
        string $htmlContent,
        string $textContent,
        array $from = [],
        array $to = []
    ) {
        $this->mailbox = $mailbox;
        $this->id = $id;
        $this->subject = $subject;
        $this->htmlContent = $htmlContent;
        $this->textContent = $textContent;
        $this->from = $from;
        $this->to = $to;
    }

    public function getId() : string
    {
        return $this->id;
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
}
