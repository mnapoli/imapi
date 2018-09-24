<?php

namespace Imapi\Query;

use PHPUnit\Framework\TestCase;

/**
 * 
 * @author Zaahid Bateson
 */
class QueryTest extends TestCase
{
    private $instance;

    protected function setUp()
    {
        $this->instance = new Query();
    }

    public function testDefaultFolder()
    {
        $this->assertEquals('INBOX', $this->instance->getFolder());
    }

    public function testSetFolder()
    {
        $this->instance->setFolder('Schmbox');
        $this->assertEquals('Schmbox', $this->instance->getFolder());
    }

    public function testDefaultYoungerThan()
    {
        $this->assertNull($this->instance->getYoungerThan());
    }

    public function testSetYoungerThan()
    {
        $this->instance->setYoungerThan(1);
        $this->assertEquals(1, $this->instance->getYoungerThan());
    }

    public function testDefaultFlags()
    {
        $this->assertEquals([], $this->instance->getFlags());
    }

    public function testSetFlag()
    {
        $this->instance->setFlag(Query::FLAG_ANSWERED, true);
        $this->instance->setFlag(Query::FLAG_DELETED, false);
        $flags = $this->instance->getFlags();
        $this->assertTrue($flags[Query::FLAG_ANSWERED]);
        $this->assertTrue(isset($flags[Query::FLAG_DELETED]));
        $this->assertFalse($flags[Query::FLAG_DELETED]);
    }
}
