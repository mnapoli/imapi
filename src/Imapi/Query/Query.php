<?php

namespace Imapi\Query;

/**
 * Query.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class Query
{
    /**
     * @var string
     */
    private $folder = 'INBOX';

    /**
     * In seconds.
     * @var int
     */
    private $youngerThan;

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
}
