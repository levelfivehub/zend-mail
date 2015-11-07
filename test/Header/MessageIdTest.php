<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Mail\Header;

use Zend\Mail\Header;

/**
 * @group      Zend_Mail
 */
class MessageIdTest extends \PHPUnit_Framework_TestCase
{
    public function testSettingManually()
    {
        $id = "CALTvGe4_oYgf9WsYgauv7qXh2-6=KbPLExmJNG7fCs9B=1nOYg@mail.example.com";
        $messageId = new Header\MessageId();
        $messageId->setId($id);

        $expected = sprintf('<%s>', $id);
        $this->assertEquals($expected, $messageId->getFieldValue());
    }

    public function testHeaderIdValid()
    {
        $headerLine = 'message-id: abc123';
        $messageId = Header\MessageId::fromString($headerLine);

        $this->assertEquals($messageId->getId(), '<abc123>');
        $this->assertEquals($messageId->toString(), 'Message-ID: <abc123>');
        $this->assertEquals($messageId->getFieldName(), 'Message-ID');
    }

    public function testAutoGeneration()
    {
        $messageId = new Header\MessageId();
        $messageId->setId();

        $this->assertContains('@', $messageId->getFieldValue());
    }

    /**
     * @return array
     */
    public function headerLines()
    {
        return [
            'newline'        => ["Message-ID: foo\nbar"],
            'cr-lf'          => ["Message-ID: bar\r\nfoo"],
            'cr-lf-wsp'      => ["Message-ID: bar\r\n\r\n baz"],
            'multiline'      => ["Message-ID: baz\r\nbar\r\nbau"],
            'invalid-string' => ["invalid: baz"],
        ];
    }

    /**
     * @dataProvider headerLines
     * @group ZF2015-04
     */
    public function testFromStringPreventsCrlfInjectionOnDetection($header)
    {
        $this->setExpectedException('Zend\Mail\Header\Exception\InvalidArgumentException');
        Header\MessageId::fromString($header);
    }

    public function invalidIdentifiers()
    {
        return [
            'newline'   => ["foo\nbar"],
            'cr-lf'     => ["bar\r\nfoo"],
            'cr-lf-wsp' => ["bar\r\n\r\n baz"],
            'multiline' => ["baz\r\nbar\r\nbau"],
            'folding'   => ["bar\r\n baz"],
        ];
    }

    /**
     * @dataProvider invalidIdentifiers
     * @group ZF2015-04
     */
    public function testInvalidIdentifierRaisesException($id)
    {
        $header = new Header\MessageId();
        $this->setExpectedException('Zend\Mail\Header\Exception\InvalidArgumentException');
        $header->setId($id);
    }
}
