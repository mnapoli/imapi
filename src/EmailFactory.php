<?php
declare(strict_types = 1);

namespace Imapi;

use DateTime;
use Horde_Imap_Client;
use Horde_Imap_Client_Data_Fetch;
use Horde_Imap_Client_Fetch_Results;
use Horde_Mail_Rfc822_Address;
use HTMLPurifier;
use HTMLPurifier_Config;
use PhpMimeMailParser\Parser;

class EmailFactory
{
    /**
     * @var HTMLPurifier
     */
    private $htmlFilter;

    public function __construct(HTMLPurifier $htmlFilter = null)
    {
        $this->htmlFilter = $htmlFilter ?: $this->createHTMLPurifier();
    }

    public function create(string $mailbox, Horde_Imap_Client_Data_Fetch $hordeEmail) : Email
    {
        // Parse the message body
        $parser = new Parser();
        $parser->setText($hordeEmail->getFullMsg());
        $htmlContent = (string) $parser->getMessageBody('html');
        $textContent = (string) $parser->getMessageBody('text');

        // Filter HTML body to have only safe HTML
        $htmlContent = trim($this->htmlFilter->purify($htmlContent));

        // If no HTML content, use the text content
        if ($htmlContent == '') {
            $htmlContent = nl2br($textContent);
        }

        // The envelope contains the headers
        $envelope = $hordeEmail->getEnvelope();

        $from = [];
        foreach ($envelope->from as $hordeFrom) {
            /** @var Horde_Mail_Rfc822_Address $hordeFrom */
            if ($hordeFrom->bare_address) {
                $from[] = new EmailAddress($hordeFrom->bare_address, $hordeFrom->personal);
            }
        }
        $to = [];
        foreach ($envelope->to as $hordeTo) {
            /** @var Horde_Mail_Rfc822_Address $hordeTo */
            if ($hordeTo->bare_address) {
                $to[] = new EmailAddress($hordeTo->bare_address, $hordeTo->personal);
            }
        }

        $messageId = $this->parseMessageId($envelope->message_id);
        $inReplyTo = $this->parseMessageId($envelope->in_reply_to);

        $message = new Email(
            (string) $hordeEmail->getUid(),
            $messageId,
            $mailbox,
            $envelope->subject,
            $htmlContent,
            $textContent,
            $from,
            $to,
            $inReplyTo
        );

        $date = new DateTime();
        $date->setTimestamp($envelope->date->getTimestamp());
        $message->setDate($date);

        $flags = $hordeEmail->getFlags();
        if (in_array(Horde_Imap_Client::FLAG_SEEN, $flags)) {
            $message->setRead(true);
        }

        return $message;
    }

    /**
     * @param Horde_Imap_Client_Data_Fetch[]|Horde_Imap_Client_Fetch_Results $hordeEmails
     * @return Email[]
     */
    public function createMany(string $mailbox, $hordeEmails) : array
    {
        $emails = [];

        foreach ($hordeEmails as $hordeEmail) {
            $emails[] = $this->create($mailbox, $hordeEmail);
        }

        return $emails;
    }

    private function createHTMLPurifier() : HTMLPurifier
    {
        return new HTMLPurifier(HTMLPurifier_Config::createDefault());
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

        if ($result === false) {
            throw new \Exception('Unexpected error while parsing message ID ' . $messageId);
        }
        if ($result === 0) {
            return null;
        }

        return $matches[1];
    }
}
