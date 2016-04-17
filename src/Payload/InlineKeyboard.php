<?php namespace korchasa\Telegram\Payload;

use korchasa\Telegram\Payload;

class InlineKeyboard extends AbstractPayload
{
    /**
     * Array of button rows, each represented by an Array of Strings
     *
     * @var string[][]
     */
    public $inline_keyboard = [];

    public function __construct($data)
    {
        $this->inline_keyboard = get($data, 'inline_keyboard', []);
    }
}
