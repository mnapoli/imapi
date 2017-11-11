<?php

namespace Imapi\Query;

/**
 * Builds a query.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class QueryBuilder
{
    /**
     * @var Query
     */
    private $query;

    public static function create(string $folder = null) : self
    {
        return new static($folder);
    }

    private function __construct(string $folder = null)
    {
        $this->query = new Query();

        if ($folder !== null) {
            $this->query->setFolder($folder);
        }
    }

    public function getQuery() : Query
    {
        return $this->query;
    }

    /**
     * @param int $interval Number of seconds (e.g. 3600 will return emails of the last hour).
     */
    public function youngerThan(int $interval) : self
    {
        $this->query->setYoungerThan($interval);

        return $this;
    }

    public function flagAnswered($value) 
    {
        $this->query->setFlags(Query::FLAG_ANSWERED, $value);
        return $this;
    }

    public function flagDeleted($value) 
    {
        $this->query->setFlags(Query::FLAG_DELETED, $value);
        return $this;
    }

    public function flagDraft($value) 
    {
        $this->query->setFlags(Query::FLAG_DRAFT, $value);
        return $this;
    }

    public function flagFlaged($value) 
    {
        $this->query->setFlags(Query::FLAG_FLAGED, $value);
        return $this;
    }

    public function flagRecent($value) 
    {
        $this->query->setFlags(Query::FLAG_RECENT, $value);
        return $this;
    }

    public function flagSeen($value)
    {
        $this->query->setFlags(Query::FLAG_SEEN, $value);
        return $this;
    }

}
