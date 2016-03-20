<?php namespace korchasa\Telegram;

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
}
