<?php namespace korchasa\Telegram;

class Message
{
    /** @var integer */
    public $message_id;
    /** @var User */
    public $from;
    /** @var integer */
    public $date;
    public $text;
    public $location;

    public function __construct(\stdClass $data = null)
    {
        if ($data) {
            $this->message_id = $data->message_id;
            $this->from = new User($data->from);
            $this->date = $data->date;
            $this->text = property_exists($data, 'text') ? $data->text : null;
            if (property_exists($data, 'location')) {
                $this->location = new Location($data->location);
            }
        }
    }
}
