<?php

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
        $id,
        $mailbox,
        $subject,
        $htmlContent,
        $textContent,
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

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getMailbox()
    {
        return $this->mailbox;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string
     */
    public function getHtmlContent()
    {
        return $this->htmlContent;
    }

    /**
     * @return string
     */
    public function getTextContent()
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

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return EmailAddress[]
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return EmailAddress[]
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param bool $read
     */
    public function setRead($read)
    {
        $this->read = (bool) $read;
    }

    /**
     * @return bool
     */
    public function isRead()
    {
        return $this->read;
    }
}
