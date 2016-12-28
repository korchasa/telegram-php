<?php namespace korchasa\Telegram\Structs\Payload;

use korchasa\Telegram\Payload;

class InlineKeyboard extends AbstractPayload
{
    /**
     * Array of button rows, each represented by an Array of Strings
     *
     * @var string[][]
     */
    public $inline_keyboard = [];

    public function __construct($arrayOfRowsOfButtons)
    {
        $this->inline_keyboard = $arrayOfRowsOfButtons;
    }
}
