<?php
declare(strict_types = 1);

namespace Imapi;

use Horde_Imap_Client;
use Horde_Imap_Client_Data_Fetch;
use Horde_Imap_Client_Fetch_Results;
use HTMLPurifier;
use HTMLPurifier_Config;
use ZBateson\MailMimeParser\MailMimeParser;
use ZBateson\MailMimeParser\Message;

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

    public function create(string $mailbox, Horde_Imap_Client_Data_Fetch $hordeEmail) : Message
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
