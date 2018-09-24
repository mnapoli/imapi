<?php

namespace Imapi;

use Horde_Imap_Client_Socket;
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
}
