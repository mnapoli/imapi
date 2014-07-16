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

    /**
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param string $folder
     */
    public function setFolder($folder)
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
    public function setYoungerThan($youngerThan)
    {
        $this->youngerThan = $youngerThan;
    }
}
