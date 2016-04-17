<?php namespace korchasa\Telegram;

use korchasa\Telegram\Message\Message;

class Update
{
    public $update_id;
    /**
     * @var Message
     */
    public $message;

    public function __construct(\stdClass $data = null)
    {
        if ($data) {
            $this->update_id = $data->update_id;
            $this->message = new Message($data->message);
        }
    }

    public function isText()
    {
        return (bool) $this->message->text;
    }
}
