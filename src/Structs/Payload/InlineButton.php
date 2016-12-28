<?php namespace korchasa\Telegram\Structs\Payload;

class InlineButton
{
    /** @var string */
    public $text;

    /** @var string */
    public $url;

    /** @var string */
    public $callback_data;

    public function __construct($text, $callback_data = null, $url = null)
    {
        $this->text = $text;
        $this->url = $url;
        $this->callback_data = $callback_data;
    }
}
