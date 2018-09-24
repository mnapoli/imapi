<?php

namespace Imapi;

use Horde_Imap_Client;
use Horde_Imap_Client_Socket;
use Horde_Imap_Client_Fetch_Query;
use Horde_Imap_Client_Ids;
use PHPUnit\Framework\TestCase;
use Imapi\Query\Query;

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
            ->with('Schmbox', $this->anything())
            ->willReturn(['match' => $mockIds]);

        $self = $this;
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
}
