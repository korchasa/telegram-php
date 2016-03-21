<?php namespace korchasa\Telegram;

class User
{
    /** @var integer */
    public $user_id;

    /** @var string */
    public $first_name;

    /** @var string */
    public $last_name;

    /** @var string */
    public $username;

    public function __construct($data = null)
    {
        if ($data) {
            $this->user_id = get($data, 'id', get($data, 'user_id'));
            $this->first_name = get($data, 'first_name');
            $this->last_name = get($data, 'last_name');
            $this->username = get($data, 'username');
        }
    }
}
