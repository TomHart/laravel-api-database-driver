<?php

namespace TomHart\Database\Tests;


use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use TomHart\Database\Database\Query\Grammars\ApiGrammar;

class GrammarTest extends TestCase
{


    /**
     * @var Grammar
     */
    private $grammar;

    /**
     * @var Builder
     */
    private $builder;

    private const FROM = 'test';

    protected function setUp(): void
    {
        parent::setUp();
        $this->grammar = new ApiGrammar();

        /** @var Connection $db */
        $db = $this->app->get('db')->connection();
        $db->setDatabaseName(self::HOST);

        $builder = new Builder($db, $this->grammar, null);
        $builder->from = self::FROM;
        $this->builder = $builder;
    }


    /**
     * Test building a simple select API call
     */
    public function testSimpleSelect(): void
    {
        $string = $this->grammar->compileSelect($this->builder);

        $this->assertSame('/' . self::FROM, $string);
    }


    /**
     * Test building a select API call with a limit.
     */
    public function testLimitedSelect(): void
    {
        $this->builder->limit(5);
        $string = $this->grammar->compileSelect($this->builder);

        $this->assertSame('/' . self::FROM . '?' . http_build_query([
                'limit' => 5
            ]), $string);
    }


    /**
     * Test building a select API call with a limit.
     */
    public function testDefaultQueryParams(): void
    {
        $this->grammar->setDefaultQueryString(['abc' => 'def']);
        $string = $this->grammar->compileSelect($this->builder);

        $this->assertSame('/' . self::FROM . '?' . http_build_query([
                'abc' => 'def'
            ]), $string);
    }

    /**
     * Test where clauses can be passed.
     */
    public function testWithSingleWhere(): void
    {
        $this->builder->where('column', 'value');
        $string = $this->grammar->compileSelect($this->builder);
        $this->assertSame('/' . self::FROM . '?' . http_build_query([
                'column' => 'value'
            ]), $string);
    }

    /**
     * Test multiple where clauses can be passed.
     */
    public function testWithMultipleWhere(): void
    {
        $this->builder->where('column', 'value');
        $this->builder->where('column2', 'value2');
        $string = $this->grammar->compileSelect($this->builder);
        $this->assertSame('/' . self::FROM . '?' . http_build_query([
                'column' => 'value',
                'column2' => 'value2'
            ]), $string);
    }

    /**
     * Test whereIn clauses can be passed.
     */
    public function testWithWhereIn(): void
    {
        $this->builder->whereIn('column', ['value', 'value2']);
        $string = $this->grammar->compileSelect($this->builder);
        $this->assertSame('/' . self::FROM . '?' . http_build_query([
                'column' => ['value', 'value2']
            ]), $string);
    }
}
