<?php namespace korchasa\Telegram\Tests\Structs;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;
use korchasa\Telegram\Telegram;
use korchasa\Telegram\Structs\Update;

class TelegramTest extends \PHPUnit_Framework_TestCase
{
    function testLoop()
    {
        $telegram = $this->telegram([
            $this->responseWithOneUpdate(1),
            $this->responseWithOneUpdate(2),
            $this->responseWithOneUpdate(null),
            $this->responseWithOneUpdate(3),
        ]);

        $updates_ids = [];
        $telegram->loop(function($update) use(&$updates_ids) {
            $this->assertInstanceOf(Update::class, $update);
            $this->assertNotNull($update->telegram);
            $updates_ids[] = $update->update_id;
        }, 4);

        $this->assertEquals([1, 2, 3], $updates_ids);
    }

    protected function telegram($responses)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        return new Telegram('nothing', null, ['handler' => $stack]);
    }

    protected function responseWithOneUpdate($update_id)
    {
        $json = '{
            "update_id": '.$update_id.',
            "message": {
                "message_id": 1024,
                "from": {
                    "id": 100,
                    "first_name": "Станислав",
                    "last_name": "Корчагин",
                    "username": "korchasa"
                },
                "chat": {
                    "id": 100,
                    "first_name": "Станислав",
                    "last_name": "Корчагин",
                    "username": "korchasa",
                    "type": "private"
                },
                "date": 1482840687,
                "text": "Please choose an action from keyboard below"
            }
        }';
        return new Response(200, [], json_encode([
            'ok' => true,
            "result" => $update_id ? [ json_decode($json) ] : []
        ]));
    }
}