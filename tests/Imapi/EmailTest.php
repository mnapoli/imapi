<?php

namespace Imapi;

use HTMLPurifier;
use ZBateson\MailMimeParser\Message;
use ZBateson\MailMimeParser\Header\DateHeader;
use ZBateson\MailMimeParser\Header\AddressHeader;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 *
 * @author Zaahid Bateson
 */
class EmailTest extends TestCase
{
    private $mockHtmlPurifier;
    private $mockMessage;
    private $uid = '007';
    private $mailbox = 'Black';
    private $read = true;
    private $instance;

    protected function setUp()
    {
        $this->mockHtmlPurifier = $this
            ->getMockBuilder(HTMLPurifier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mockMessage = $this
            ->getMockBuilder(Message::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->instance = new Email(
            $this->mockHtmlPurifier,
            $this->uid,
            $this->mailbox,
            $this->read,
            $this->mockMessage
        );
    }

    public function testInstance()
    {
        $this->assertEquals($this->uid, $this->instance->getUid());
        $this->assertEquals($this->mailbox, $this->instance->getMailbox());
        $this->assertTrue($this->instance->isRead());
        $this->assertEquals($this->mockMessage, $this->instance->getMessage());
    }

    public function testGetMessageId()
    {
        $this->mockMessage->expects($this->atLeastOnce())
            ->method('getHeaderValue')
            ->with('Message-ID')
            ->willReturnOnConsecutiveCalls(null, 'spooky', '<sp00ky>');
        $this->assertNull($this->instance->getMessageId());
        $this->assertNull($this->instance->getMessageId());
        $this->assertEquals('sp00ky', $this->instance->getMessageId());
    }

    public function testGetInReplyTo()
    {
        $this->mockMessage->expects($this->atLeastOnce())
            ->method('getHeaderValue')
            ->with('In-Reply-To')
            ->willReturnOnConsecutiveCalls(null, 'spooky', '<sp00ky>');
        $this->assertNull($this->instance->getInReplyTo());
        $this->assertNull($this->instance->getInReplyTo());
        $this->assertEquals('sp00ky', $this->instance->getInReplyTo());
    }

    public function testGetHtmlContent()
    {
        $this->mockMessage->expects($this->once())
            ->method('getHtmlContent')
            ->willReturn('<u>redacted</u>');
        $this->assertEquals('<u>redacted</u>', $this->instance->getHtmlContent());
    }

    public function testGetTextContent()
    {
        $this->mockMessage->expects($this->once())
            ->method('getTextContent')
            ->willReturn('_redacted_');
        $this->assertEquals('_redacted_', $this->instance->getTextContent());
    }

    public function testGetSanitizedHtmlContent()
    {
        $this->mockMessage->expects($this->once())
            ->method('getHtmlContent')
            ->willReturn('<u>redacted</u>');
        $this->mockHtmlPurifier->expects($this->once())
            ->method('purify')
            ->with('<u>redacted</u>')
            ->willReturn('  *purified*   ');
        
        $this->assertEquals('*purified*', $this->instance->getSanitizedHtmlContent());
    }

    public function testGetDate()
    {
        $dateMock = $this
            ->getMockBuilder(DateHeader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateTime = new DateTime;
        $dateMock->expects($this->once())
            ->method('getDateTime')
            ->willReturn($dateTime);

        $this->mockMessage->expects($this->exactly(2))
            ->method('getHeader')
            ->willReturnOnConsecutiveCalls(null, $dateMock);
        $this->assertNull($this->instance->getDate());
        $this->assertEquals($dateTime, $this->instance->getDate());
    }

    public function testGetFrom()
    {
        $addrMock = $this
            ->getMockBuilder(AddressHeader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $addrMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn(['Behind ya']);

        $this->mockMessage->expects($this->exactly(2))
            ->method('getHeader')
            ->willReturnOnConsecutiveCalls(null, $addrMock);
        $this->assertNull($this->instance->getFrom());
        $this->assertEquals(['Behind ya'], $this->instance->getFrom());
    }

    public function testGetTo()
    {
        $addrMock = $this
            ->getMockBuilder(AddressHeader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $addrMock->expects($this->once())
            ->method('getAddresses')
            ->willReturn(['Behind ya']);

        $this->mockMessage->expects($this->exactly(2))
            ->method('getHeader')
            ->willReturnOnConsecutiveCalls(null, $addrMock);
        $this->assertNull($this->instance->getTo());
        $this->assertEquals(['Behind ya'], $this->instance->getTo());
    }
}
