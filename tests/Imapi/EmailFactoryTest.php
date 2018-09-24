<?php

namespace Imapi;

use Horde_Imap_Client;
use Horde_Imap_Client_Data_Fetch;
use HTMLPurifier;
use ZBateson\MailMimeParser\MailMimeParser;
use ZBateson\MailMimeParser\Message;
use PHPUnit\Framework\TestCase;

/**
 *
 * @author Zaahid Bateson
 */
class EmailFactoryTest extends TestCase
{
    private $mockHtmlPurifier;
    private $mockParser;
    private $instance;

    protected function setUp()
    {
        $this->mockHtmlPurifier = $this
            ->getMockBuilder(HTMLPurifier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockParser = $this
            ->getMockBuilder(MailMimeParser::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->instance = new EmailFactory(
            $this->mockHtmlPurifier, $this->mockParser
        );
    }

    public function testCreate()
    {
        $mockHordeEmail = $this
            ->getMockBuilder(Horde_Imap_Client_Data_Fetch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockHordeEmail->expects($this->once())
            ->method('getFullMsg')
            ->with(true)
            ->willReturn('Stop your messing around');
        $mockHordeEmail->expects($this->once())
            ->method('getFlags')
            ->willReturn([]);

        $mockMessage = $this
            ->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockParser->expects($this->once())
            ->method('parse')
            ->with('Stop your messing around')
            ->willReturn($mockMessage);

        $ret = $this->instance->create('RudeBoy', $mockHordeEmail);
        $this->assertEquals('RudeBoy', $ret->getMailbox());
        $this->assertEquals($mockMessage, $ret->getMessage());
        $this->assertFalse($ret->isRead());
    }

    public function testCreateFlagSeen()
    {
        $mockHordeEmail = $this
            ->getMockBuilder(Horde_Imap_Client_Data_Fetch::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockHordeEmail->expects($this->once())
            ->method('getFlags')
            ->willReturn([Horde_Imap_Client::FLAG_SEEN]);

        $mockMessage = $this
            ->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockParser->expects($this->once())
            ->method('parse')
            ->willReturn($mockMessage);

        $ret = $this->instance->create('RudeBoy', $mockHordeEmail);
        $this->assertEquals('RudeBoy', $ret->getMailbox());
        $this->assertEquals($mockMessage, $ret->getMessage());
        $this->assertTrue($ret->isRead());
    }

    public function testCreateMany()
    {
        $mockEmails = [];
        $mockMessages = [];

        for ($i = 0; $i < 3; ++$i) {
            $mockEmails[$i] = $this
                ->getMockBuilder(Horde_Imap_Client_Data_Fetch::class)
                ->disableOriginalConstructor()
                ->getMock();
            $mockMessages[$i] = $this
                ->getMockBuilder(Message::class)
                ->disableOriginalConstructor()
                ->getMock();

            $mockEmails[$i]->expects($this->once())
                ->method('getFlags')
                ->willReturn([]);
        }

        $this->mockParser->expects($this->exactly(3))
            ->method('parse')
            ->willReturnOnConsecutiveCalls($mockMessages[0], $mockMessages[1], $mockMessages[2]);

        $ret = $this->instance->createMany('RudeBoy', $mockEmails);
        $this->assertCount(3, $ret);
        $this->assertEquals('RudeBoy', $ret[0]->getMailbox());
        $this->assertEquals($mockMessages[0], $ret[0]->getMessage());
        $this->assertEquals($mockMessages[1], $ret[1]->getMessage());
        $this->assertEquals($mockMessages[2], $ret[2]->getMessage());
    }
}
