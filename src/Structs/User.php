<?php namespace korchasa\Telegram\Structs;

use korchasa\Telegram\Unstructured;

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

    public function __construct(Unstructured $std = null)
    {
        if ($std) {
            $this->user_id = $std->scalar('id', $std->scalar('user_id'));
            $this->first_name = $std->scalar('first_name');
            $this->last_name = $std->scalar('last_name');
            $this->username = $std->scalar('username');
        }
    }
}
