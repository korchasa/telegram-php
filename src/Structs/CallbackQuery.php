<?php namespace korchasa\Telegram\Structs;

use korchasa\Telegram\Structs\User;
use korchasa\Telegram\Structs\Message;
use korchasa\Telegram\Unstructured;

/**
 * Class Callback_query
 */
class CallbackQuery
{
    /**
     * @var string
     *
     */
    public $id;

    /**
     * @var User
     */
    public $from;

    /**
     * @var Message|null
     */
    public $message;

    /**
     * @var string|null
     */
    public $chat_instance;

    /**
     * @var string|null
     */
    public $data;

    public function __construct(Unstructured $std)
    {
        $this->id = $std->scalar('id');
        $this->from = $std->object('from', User::class);
        $this->message = $std->object('message', Message::class);
        $this->chat_instance = $std->scalar('chat_instance');
        $this->data = $std->scalar('data');
    }
}
