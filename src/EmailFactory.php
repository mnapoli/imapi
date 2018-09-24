<?php
declare(strict_types = 1);

namespace Imapi;

use Horde_Imap_Client;
use Horde_Imap_Client_Data_Fetch;
use Horde_Imap_Client_Fetch_Results;
use HTMLPurifier;
use HTMLPurifier_Config;
use ZBateson\MailMimeParser\MailMimeParser;

class EmailFactory
{
    /**
     * @var HTMLPurifier
     */
    private $htmlFilter;

    /**
     * @var MailMimeParser
     */
    private $parser;

    /**
     *
     * @param HTMLPurifier $htmlFilter
     * @param MailMimeParser $parser
     */
    public function __construct(HTMLPurifier $htmlFilter = null, MailMimeParser $parser = null)
    {
        $this->htmlFilter = $htmlFilter ?: $this->createHTMLPurifier();
        $this->parser = $parser ?: new MailMimeParser();
    }

    /**
     * Creates and returns an Email object out of the passed $mailbox and Horde
     * email object.
     *
     * @param string $mailbox
     * @param Horde_Imap_Client_Data_Fetch $hordeEmail
     * @return \Imapi\Email
     */
    public function create(string $mailbox, Horde_Imap_Client_Data_Fetch $hordeEmail) : Email
    {
        // Parse the message body
        $message = $this->parser->parse($hordeEmail->getFullMsg(true));

        $read = false;
        $flags = $hordeEmail->getFlags();
        if (in_array(Horde_Imap_Client::FLAG_SEEN, $flags)) {
            $read = true;
        }

        return new Email(
            $this->htmlFilter,
            (string) $hordeEmail->getUid(),
            $mailbox,
            $read,
            $message
        );
    }

    /**
     * Creates and returns an array of Email objects from the passed Horde
     * emails.
     *
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
