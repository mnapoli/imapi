<?php

namespace Imapi;

use Horde_Imap_Client;
use Horde_Imap_Client_Socket;
use Horde_Imap_Client_Fetch_Query;
use Horde_Imap_Client_Ids;
use Horde_Imap_Client_Search_Query;
use Imapi\Query\Query;
use PHPUnit\Framework\TestCase;

/**
 * 
 * @author Zaahid Bateson
 */
class ClientTest extends TestCase
{
    private $mockHordeSocket;
    private $mockEmailFactory;
    private $instance;

    protected function setUp()
    {
        $this->mockHordeSocket = $this
            ->getMockBuilder(Horde_Imap_Client_Socket::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockEmailFactory = $this
            ->getMockBuilder(EmailFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->instance = new Client(
            $this->mockHordeSocket, $this->mockEmailFactory
        );
    }

    public function testGetFolders()
    {
        $this->mockHordeSocket
            ->expects($this->once())
            ->method('listMailboxes')
            ->with('*')
            ->willReturn([
                'folder1' => 'not checked',
                'folder2' => 'not checked',
                'folder3' => 'not checked',
            ]);
        $ret = $this->instance->getFolders();
        $this->assertNotEmpty($ret);
        $this->assertEquals(['folder1', 'folder2', 'folder3'], $ret);
    }

    public function testGetEmails()
    {
        $mockQuery = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockQuery->expects($this->once())
            ->method('getFlags')
            ->willReturn([]);
        $mockQuery->expects($this->once())
            ->method('getFolder')
            ->willReturn('Schmbox');

        $mockIds = $this->getMockBuilder(Horde_Imap_Client_Ids::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockIds->ids = [1, 2, 3];
        $this->mockHordeSocket->expects($this->once())
            ->method('search')
            ->with(
                'Schmbox',
                $this->callback(function ($ob) {
                    $this->assertInstanceOf(Horde_Imap_Client_Search_Query::class, $ob);
                    return true;
                })
            )
            ->willReturn(['match' => $mockIds]);

        $this->mockHordeSocket->expects($this->once())
            ->method('fetch')
            ->with(
                'Schmbox',
                $this->callback(function ($ob) {
                    $this->assertTrue(isset($ob[Horde_Imap_Client::FETCH_ENVELOPE]));
                    $this->assertTrue($ob[Horde_Imap_Client::FETCH_ENVELOPE]);

                    $this->assertInstanceOf(Horde_Imap_Client_Fetch_Query::class, $ob);
                    $this->assertNotEmpty($ob[Horde_Imap_Client::FETCH_FULLMSG]);
                    $this->assertTrue(isset($ob[Horde_Imap_Client::FETCH_FULLMSG]['peek']));
                    $this->assertTrue($ob[Horde_Imap_Client::FETCH_FULLMSG]['peek']);

                    $this->assertTrue(isset($ob[Horde_Imap_Client::FETCH_FLAGS]));
                    $this->assertTrue($ob[Horde_Imap_Client::FETCH_FLAGS]);
                    return true;
                }),
                $this->callback(function ($arr) {
                    $this->assertArrayHasKey('ids', $arr);
                    $this->assertInstanceOf(Horde_Imap_Client_Ids::class, $arr['ids']);
                    $this->assertEquals([1, 2, 3], $arr['ids']->ids);
                    return true;
                })
            )
            ->willReturn('Los Emails');

        $this->mockEmailFactory->expects($this->once())
            ->method('createMany')
            ->with('Schmbox', 'Los Emails')
            ->willReturn(['Los Parsed Messages']);

        $ret = $this->instance->getEmails($mockQuery);
        $this->assertEquals(['Los Parsed Messages'], $ret);
    }

    public function testGetEmailsCreatesQuery()
    {
        $this->mockEmailFactory->expects($this->once())
            ->method('createMany')
            ->with('INBOX', 'Los Emails')
            ->willReturn(['Los Parsed Messages']);

        $mockIds = $this->getMockBuilder(Horde_Imap_Client_Ids::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockIds->ids = [];
        $this->mockHordeSocket->expects($this->once())
            ->method('search')
            ->with(
                'INBOX',
                $this->callback(function ($ob) {
                    $this->assertInstanceOf(Horde_Imap_Client_Search_Query::class, $ob);
                    return true;
                })
            )
            ->willReturn(['match' => $mockIds]);

        $this->mockHordeSocket->expects($this->once())
            ->method('fetch')
            ->with(
                'INBOX',
                $this->anything(),
                $this->anything()
            )
            ->willReturn('Los Emails');

        $ret = $this->instance->getEmails();
        $this->assertEquals(['Los Parsed Messages'], $ret);
    }

    public function testGetEmailsWithIntervalSearch()
    {
        $mockQuery = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockQuery->expects($this->once())
            ->method('getFlags')
            ->willReturn([]);
        $mockQuery->expects($this->once())
            ->method('getFolder')
            ->willReturn('Schmbox');
        $mockQuery->expects($this->exactly(2))
            ->method('getYoungerThan')
            ->willReturn(2);

        $mockIds = $this->getMockBuilder(Horde_Imap_Client_Ids::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockIds->ids = [1, 2, 3];
        $this->mockHordeSocket->expects($this->once())
            ->method('search')
            ->with(
                'Schmbox',
                $this->callback(function ($ob) {
                    $this->assertInstanceOf(Horde_Imap_Client_Search_Query::class, $ob);
                    // no public interface to check the younger than search query
                    return true;
                })
            )
            ->willReturn(['match' => $mockIds]);

        $this->mockHordeSocket->expects($this->once())
            ->method('fetch')
            ->willReturn('Los Emails');

        $this->mockEmailFactory->expects($this->once())
            ->method('createMany')
            ->with('Schmbox', 'Los Emails')
            ->willReturn(['Los Parsed Messages']);

        $ret = $this->instance->getEmails($mockQuery);
        $this->assertEquals(['Los Parsed Messages'], $ret);
    }

    public function testGetEmailsSetFlags()
    {
        $mockQuery = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockQuery->expects($this->atLeastOnce())
            ->method('getFlags')
            ->willReturn([Query::FLAG_ANSWERED => true, Query::FLAG_DELETED => false]);
        $mockQuery->expects($this->once())
            ->method('getFolder')
            ->willReturn('Schmbox');
        
        $mockIds = $this->getMockBuilder(Horde_Imap_Client_Ids::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockIds->ids = [1, 2, 3];
        $this->mockHordeSocket->expects($this->once())
            ->method('search')
            ->with(
                'Schmbox',
                $this->callback(function ($ob) {
                    $this->assertInstanceOf(Horde_Imap_Client_Search_Query::class, $ob);
                    // no public interface to check the set flags
                    return true;
                })
            )
            ->willReturn(['match' => $mockIds]);

        $this->mockHordeSocket->expects($this->once())
            ->method('fetch')
            ->willReturn('Los Emails');

        $this->mockEmailFactory->expects($this->once())
            ->method('createMany')
            ->with('Schmbox', 'Los Emails')
            ->willReturn(['Los Parsed Messages']);

        $ret = $this->instance->getEmails($mockQuery);
        $this->assertEquals(['Los Parsed Messages'], $ret);
    }
}
