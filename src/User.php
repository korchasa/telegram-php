<?php namespace korchasa\Telegram;

class User
{
    public $user_id;
    public $first_name;
    public $last_name;
    public $username;

    public function __construct(\stdClass $data = null)
    {
        if ($data) {
            $this->user_id = get($data, 'id', get($data, 'user_id'));
            $this->first_name = get($data, 'first_name');
            $this->last_name = get($data, 'last_name');
            $this->username = get($data, 'username');
        }
    }
}
