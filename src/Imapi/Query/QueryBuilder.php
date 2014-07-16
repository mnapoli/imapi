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

    /**
     * @param string|null $folder
     * @return QueryBuilder
     */
    public static function create($folder = null)
    {
        return new static($folder);
    }

    private function __construct($folder = null)
    {
        $this->query = new Query();

        if ($folder !== null) {
            $this->query->setFolder($folder);
        }
    }

    /**
     * @return Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param int $interval Number of seconds (e.g. 3600 will return emails of the last hour).
     * @return $this
     */
    public function youngerThan($interval)
    {
        $this->query->setYoungerThan($interval);

        return $this;
    }
}
