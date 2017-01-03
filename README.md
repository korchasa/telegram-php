# telegram-php

Lightweight wrapper for [Telegram Bot API](https://core.telegram.org/bots/api).

[![Build Status](https://travis-ci.org/korchasa/telegram-php.svg)](https://travis-ci.org/korchasa/telegram-php)

## Installing

composer require korchasa/telegram-php

## Basic usage
Webhook:
```php
<?php
use korchasa\Telegram\Telegram;
use korchasa\Telegram\Structs\Chat;

//subscribe
$telegram = new Telegram('bot123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11');
$telegram->setWebhook('http://example.com/mybot');

...

//processing
$chat = new Chat();
$chat->id = 11150101;
$telegram->sendTypingAction($chat);
...
$telegram->sendMessage($chat, 'some_text');
```
Infinite loop:
```php
$telegram = new Telegram('bot123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11', 'telegram.log');
$telegram->loop(function($update) {
  $update->replyMessage('Red or blue?', new InlineKeyboard([
    new InlineButton('Foo', 'some data 1'),
    new InlineButton('Bar', 'some data 2'),
    new InlineButton('Wtf?', null, 'https://en.wikipedia.org/wiki/Foobar'),
  ]);
});
```
