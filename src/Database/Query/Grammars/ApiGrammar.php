<?php

namespace TomHart\Database\Database\Query\Grammars;


use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;

class ApiGrammar extends Grammar
{

    private $defaultQueryString = [];

    /**
     * @return array
     */
    public function getDefaultQueryString(): array
    {
        return $this->defaultQueryString;
    }

    /**
     * @param array $defaultQueryString
     * @return ApiGrammar
     */
    public function setDefaultQueryString(array $defaultQueryString): self
    {
        $this->defaultQueryString = $defaultQueryString;
        return $this;
    }

    /**
     * Compile a select statement.
     * @param Builder $query
     * @return string
     */
    public function compileSelect(Builder $query)
    {
        $queryString = $this->getDefaultQueryString();
        if ($query->limit) {
            $queryString[config('api-database.query_string_keys.limit', 'limit')] = $query->limit;
        }

        // Add the where clauses to the query string.
        foreach ($query->wheres as $where) {
            $queryString = $this->addWhereClause($where, $queryString);
        }

        if (empty($queryString)) {
            return '/' . $query->from;
        }

        return '/' . $query->from . '?' . http_build_query($queryString);
    }

    /**
     * Adds a where clause to the query string.
     * @param array $where
     * @param array $queryString
     * @return array
     */
    private function addWhereClause(array $where, array $queryString): array
    {
        switch ($where['type']) {
            default:
                $queryString[$where['column']] = $where['value'];
                break;
            case 'In':
                $queryString[$where['column']] = $where['values'];
                break;
        }

        return $queryString;
    }
}