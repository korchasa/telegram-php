<?php namespace korchasa\Telegram\Structs;

use korchasa\Telegram\Structs\User;
use korchasa\Telegram\Unstructured;

class Message
{
    /** @var integer */
    public $message_id;
    /** @var User */
    public $from;
    /** @var integer */
    public $date;
    /** @var string */
    public $text = '';
    /** @var Location */
    public $location;
    /** @var Message */
    public $reply_to_message;
    /** @var Chat */
    public $chat;

    public function __construct(Unstructured $std = null)
    {
        if ($std) {
            $this->message_id = $std->scalar('message_id');
            $this->from = $std->object('from', User::class);
            $this->date = $std->scalar('date');
            $this->text = $std->scalar('text');
            $this->location = $std->object('location', Location::class);
            $this->reply_to_message = $std->object('reply_to_message', Message::class);
            $this->chat = $std->object('chat', Chat::class);
        }
    }

    public function startWith($needles)
    {
        foreach ((array) $needles as $needle)
        {
            if ($needle != '' && strpos($this->text, $needle) === 0) return true;
        }

        return false;
    }

    public function contains($needles)
    {
        foreach ((array) $needles as $needle)
        {
            if ($needle != '' && strpos($this->text, $needle) !== false) return true;
        }

        return false;
    }
}
