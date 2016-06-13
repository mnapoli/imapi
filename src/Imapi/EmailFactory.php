<?php

namespace Imapi;

use DateTime;
use Horde_Imap_Client;
use Horde_Imap_Client_Data_Fetch;
use Horde_Imap_Client_Fetch_Results;
use Horde_Mail_Rfc822_Address;
use HTMLPurifier;
use HTMLPurifier_Config;
use MimeMailParser\Parser;

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
        $htmlContent = utf8_encode($parser->getMessageBody('html'));
        $textContent = utf8_encode($parser->getMessageBody('text'));

        // Filter HTML body to have only safe HTML
        $htmlContent = trim($this->htmlFilter->purify($htmlContent));

        // If no HTML content, use the text content
        if ($htmlContent == '') {
            $htmlContent = nl2br($textContent);
        }

        $from = [];
        foreach ($hordeEmail->getEnvelope()->from as $hordeFrom) {
            /** @var Horde_Mail_Rfc822_Address $hordeFrom */
            $from[] = new EmailAddress($hordeFrom->bare_address, $hordeFrom->personal);
        }
        $to = [];
        foreach ($hordeEmail->getEnvelope()->to as $hordeTo) {
            /** @var Horde_Mail_Rfc822_Address $hordeTo */
            $to[] = new EmailAddress($hordeTo->bare_address, $hordeTo->personal);
        }

        $message = new Email(
            $hordeEmail->getUid(),
            $mailbox,
            $hordeEmail->getEnvelope()->subject,
            $htmlContent,
            $textContent,
            $from,
            $to
        );

        $date = new DateTime();
        $date->setTimestamp($hordeEmail->getEnvelope()->date->getTimestamp());
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
}
