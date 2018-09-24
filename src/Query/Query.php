<?php

namespace Imapi\Query;

use Horde_Imap_Client;

/**
 * Query.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Query
{
    const FLAG_ANSWERED = Horde_Imap_Client::FLAG_ANSWERED;
    const FLAG_DELETED = Horde_Imap_Client::FLAG_DELETED;
    const FLAG_DRAFT = Horde_Imap_Client::FLAG_DRAFT;
    const FLAG_FLAGGED = Horde_Imap_Client::FLAG_FLAGGED;
    const FLAG_RECENT = Horde_Imap_Client::FLAG_RECENT;
    const FLAG_SEEN = Horde_Imap_Client::FLAG_SEEN;

    /**
     * @var string
     */
    private $folder = 'INBOX';

    /**
     * In seconds.
     * @var int
     */
    private $youngerThan;

    /**
     * @var array
     */
    private $flags = [];

    public function getFolder() : string
    {
        return $this->folder;
    }

    public function setFolder(string $folder)
    {
        $this->folder = $folder;
    }

    /**
     * @return int|null
     */
    public function getYoungerThan()
    {
        return $this->youngerThan;
    }

    /**
     * @param int $youngerThan Number of seconds (e.g. 3600 will return emails of the last hour).
     */
    public function setYoungerThan(int $youngerThan)
    {
        $this->youngerThan = $youngerThan;
    }
    
    public function setFlag(string $key, bool $set)
    {
        $this->flags[$key] = $set;
    }
    
    public function getFlags(): array
    {
        return $this->flags;
    }
}
