<?php namespace korchasa\Telegram\Tests;

use korchasa\Telegram\Structs\Chat;
use korchasa\Vhs\VhsTestCase;
use PHPUnit\Framework\TestCase;
use korchasa\Telegram\Structs\Payload\InlineButton;
use korchasa\Telegram\Structs\Payload\InlineKeyboard;
use korchasa\Telegram\Structs\Payload\ReplyButton;
use korchasa\Telegram\Structs\Payload\HideKeyboard;
use korchasa\Telegram\Structs\Payload\ReplyKeyboard;
use korchasa\Telegram\Structs\Message;
use korchasa\Telegram\Telegram;
use korchasa\Telegram\Structs\Update;

class IntegrationTest extends TestCase
{
    use VhsTestCase;

    /** @var Telegram */
    protected $telegram;
    /** @var Chat */
    protected $chat;

    public function setUp()
    {
        $this->telegram = new Telegram('any');
        $this->telegram->setClient($this->connectVhs($this->telegram->getClient()));
        $this->chat = new Chat();
        $this->chat->id = 11111111;
        $this->chat->first_name = 'first_name';
    }

    public function testGetUpdates()
    {
        $this->assertVhs(function () {
            $updates = $this->telegram->getUpdates();
            static::assertGreaterThanOrEqual(1, count($updates));
            foreach ($updates as $update) {
                static::assertInstanceOf(Update::class, $update);
                static::assertInternalType('integer', $update->update_id);
                static::assertInstanceOf(Message::class, $update->message);
            }
        });
    }

    /**
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function testSendMessageJustText()
    {
        $this->assertVhs(function () {
            $this->assertNotNull($this->telegram->sendMessage(
                $this->chat,
                'just text'
            ));
        });
    }

    /**
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function testSendMessageCommonKeyboard()
    {
        $this->assertVhs(function () {
            $this->assertNotNull($this->telegram->sendMessage(
                $this->chat,
                'reply keyboard',
                new ReplyKeyboard([
                    [ 'foo', 'bar' ],
                    [
                        new ReplyButton('request contact', true),
                        new ReplyButton('request location', false, true),
                    ],
                ])
            ));
        });
    }

    /**
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function testSendMessageHideKeyboard()
    {
        $this->assertVhs(function () {
            $this->assertNotNull($this->telegram->sendMessage(
                $this->chat,
                'hide reply keyboard',
                new HideKeyboard()
            ));
        });
    }

    /**
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function testSendMessageInlineKeyboard()
    {
        $this->assertVhs(function () {
            $this->assertNotNull($this->telegram->sendMessage(
                $this->chat,
                'inline keyboard',
                new InlineKeyboard([
                    [
                        new InlineButton('url', null, 'https://google.com/'),
                        new InlineButton('callback_data', 'foo'),
                    ]
                ])
            ));
        });
    }
}
