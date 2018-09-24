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

    public function flagAnswered(bool $set) : self
    {
        $this->query->setFlags(Query::FLAG_ANSWERED, $set);
        return $this;
    }

    public function flagDeleted(bool $set) : self
    {
        $this->query->setFlags(Query::FLAG_DELETED, $set);
        return $this;
    }

    public function flagDraft(bool $set) : self
    {
        $this->query->setFlags(Query::FLAG_DRAFT, $set);
        return $this;
    }

    public function flagFlaged(bool $set) : self
    {
        $this->query->setFlags(Query::FLAG_FLAGED, $set);
        return $this;
    }

    public function flagRecent(bool $set) : self
    {
        $this->query->setFlags(Query::FLAG_RECENT, $set);
        return $this;
    }

    public function flagSeen(bool $set) : self
    {
        $this->query->setFlags(Query::FLAG_SEEN, $set);
        return $this;
    }
}
