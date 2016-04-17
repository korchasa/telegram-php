<?php namespace korchasa\Telegram\Payload;

class InlineButton
{
    /** @var string */
    public $text;

    /** @var string */
    public $url;

    /** @var string */
    public $callback_data;

    /** @var string */
    public $switch_inline_query;

    public function __construct($data)
    {
        $this->text = get($data, 'text', '');
        $this->url = get($data, 'url');
        $this->callback_data = get($data, 'callback_data');
        $this->switch_inline_query = get($data, 'switch_inline_query');
    }
}
