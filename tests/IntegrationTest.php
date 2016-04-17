<?php namespace korchasa\Telegram\Test;

use korchasa\Telegram\Payload\InlineButton;
use korchasa\Telegram\Payload\InlineKeyboard;
use korchasa\Telegram\Payload\ReplyButton;
use korchasa\Telegram\Payload\HideKeyboard;
use korchasa\Telegram\Payload\ReplyKeyboard;
use korchasa\Telegram\Message\Message;
use korchasa\Telegram\Telegram;
use korchasa\Telegram\Update;
use korchasa\Telegram\User;

class IntegrationTest extends \PHPUnit_Framework_TestCase
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
            die('Usage: TOKEN=12345678:SOMELETTERS USER_ID=1234567 phpunit');
        }

        $this->telegram = new Telegram($token);
        $this->user = new User([
            'user_id' => $user_id,
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'username' => 'username',
        ]);
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
                    'keyboard' => [
                        [ 'foo', 'bar' ],
                        [
                            new ReplyButton([
                                'text'            => 'request contact',
                                'request_contact' => true
                            ]),
                            new ReplyButton([
                                'text'             => 'request location',
                                'request_location' => true
                            ]),
                        ],
                    ]
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
                    'inline_keyboard' => [
                        [
                            new InlineButton([
                                'text' => 'url',
                                'url' => 'https://google.com/',
                            ]),
                            new InlineButton([
                                'text' => 'callback_data',
                                'callback_data' => 'foo',
                            ]),
                        ],
                        [
                            new InlineButton([
                                'text' => 'switch_inline_query',
                                'switch_inline_query' => 'switch_inline_query text',
                            ]),
                        ]
                    ]
                ]
            )
        );
    }
}
