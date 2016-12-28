<?php namespace korchasa\Telegram\Structs;

use korchasa\Telegram\Unstructured;

/**
 * Class Chat
 */
class Chat
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $first_name;

    /**
     * @var string
     */
    public $last_name;

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $type;

    public function __construct(Unstructured $std)
    {
        if ($std) {
            $this->id = $std->scalar('id');
            $this->first_name = $std->scalar('first_name');
            $this->last_name = $std->scalar('last_name');
            $this->first_name = $std->scalar('first_name');
            $this->username = $std->scalar('username');
            $this->type = $std->scalar('type');
        }
    }
}
