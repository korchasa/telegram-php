<?php namespace korchasa\Telegram\Structs\Payload;

class ReplyButton
{
    /** @var string */
    public $text;

    /** @var bool */
    public $request_contact;

    /** @var bool */
    public $request_location;

    public function __construct($text, $request_contact = false, $request_location = false)
    {
        $this->text = $text;
        $this->request_contact = $request_contact;
        $this->request_location = $request_location;
    }
}
