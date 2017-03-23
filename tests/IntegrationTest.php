<?php namespace korchasa\Telegram\Tests;

use PHPUnit\Framework\TestCase;
use korchasa\Telegram\Structs\Payload\InlineButton;
use korchasa\Telegram\Structs\Payload\InlineKeyboard;
use korchasa\Telegram\Structs\Payload\ReplyButton;
use korchasa\Telegram\Structs\Payload\HideKeyboard;
use korchasa\Telegram\Structs\Payload\ReplyKeyboard;
use korchasa\Telegram\Structs\Message;
use korchasa\Telegram\Telegram;
use korchasa\Telegram\Structs\Update;
use korchasa\Telegram\Structs\User;

class IntegrationTest extends TestCase
{
    /** @var Telegram */
    protected $telegram;
    /** @var User */
    protected $user;

    public function setUp()
    {
        $token = getenv('TOKEN');
        $user_id = getenv('USER_ID');
        if (!$token || !$user_id) {
            $this->markTestSkipped('Usage: TOKEN=12345678:SOMELETTERS USER_ID=1234567 phpunit');
        }

        $this->telegram = new Telegram($token);
        $this->user = new User();
        $this->user->user_id = $user_id;
        $this->user->first_name = 'first_name';
    }

    public function testGetUpdates()
    {
        $updates = $this->telegram->getUpdates();
        static::assertGreaterThanOrEqual(1, count($updates));
        foreach ($updates as $update) {
            static::assertInstanceOf(Update::class, $update);
            static::assertInternalType('integer', $update->update_id);
            static::assertInstanceOf(Message::class, $update->message);
        }
        $this->user = $updates[0]->message->from;
    }

    /**
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function testSendMessage_JustText()
    {
        $this->telegram->sendMessage(
            $this->user,
            'just text'
        );
    }

    /**
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function testSendMessage_CommonKeyboard()
    {
        $this->telegram->sendMessage(
            $this->user,
            'reply keyboard',
            new ReplyKeyboard([
                    [ 'foo', 'bar' ],
                    [
                        new ReplyButton('request contact', true),
                        new ReplyButton('request location', false, true),
                    ],
                ]
            )
        );
    }

    /**
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function testSendMessage_HideKeyboard()
    {
        $this->telegram->sendMessage(
            $this->user,
            'hide reply keyboard',
            new HideKeyboard()
        );
    }

    /**
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function testSendMessage_InlineKeyboard()
    {
        $this->telegram->sendMessage(
            $this->user,
            'inline keyboard',
            new InlineKeyboard([
                [
                    new InlineButton('url', null, 'https://google.com/'),
                    new InlineButton('callback_data', 'foo'),
                ]
            ])
        );
    }
}
