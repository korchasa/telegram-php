<?php namespace korchasa\Telegram\Payload;

class ReplyButton
{
    /** @var string */
    public $text;

    /** @var bool */
    public $request_contact;

    /** @var bool */
    public $request_location;

    public function __construct($data)
    {
        $this->text = get($data, 'text', '');
        $this->request_contact = get($data, 'request_contact', false);
        $this->request_location = get($data, 'request_location', false);
    }
}
