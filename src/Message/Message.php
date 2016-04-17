<?php namespace korchasa\Telegram\Message;

use korchasa\Telegram\User;

class Message
{
    /** @var integer */
    public $message_id;
    /** @var User */
    public $from;
    /** @var integer */
    public $date;
    /** @var string */
    public $text;
    /** @var Location */
    public $location;

    public function __construct($data)
    {
        $this->message_id = get($data, 'message_id');
        $this->from = new User(get($data, 'from'));
        $this->date = get($data, 'date');
        $this->text = get($data, 'text');
        if (property_exists($data, 'location')) {
            $this->location = new Location($data->location);
        }
    }
}
