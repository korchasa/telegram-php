<?php namespace korchasa\Telegram\Structs;

use korchasa\Telegram\Unstructured;

class Update
{
    /**
     * @var integer
     */
    public $update_id;
    /**
     * @var Message|null
     */
    public $message;

    /**
     * @var Message|null
     */
    public $edited_message;

    /**
     * @var Message|null
     */
    public $channel_post;

    /**
     * @var Message|null
     */
    public $edited_channel_post;

    /**
     * @var CallbackQuery|null
     */
    public $callback_query;

    public function __construct(Unstructured $std = null)
    {
        if ($std) {
            $this->update_id = $std->scalar('update_id');
            $this->message = $std->object('message', Message::class);
            $this->edited_message = $std->object('edited_message', Message::class);
            $this->channel_post = $std->object('channel_post', Message::class);
            $this->edited_channel_post = $std->object('edited_channel_post', Message::class);
            $this->callback_query = $std->object('callback_query', CallbackQuery::class);
        }
    }

    public function isText()
    {
        return (bool) $this->message->text;
    }

    public function isReply()
    {
        return (bool) ($this->message && $this->message->reply_to_message);
    }

    public function isCallbackQuery()
    {
        return (bool) $this->callback_query;
    }

    public function chat()
    {
        return $this->message()->chat;
    }

    public function message()
    {
        if ($this->message) {
            return $this->message;
        } elseif ($this->callback_query) {
            return $this->callback_query->message;
        }
    }
}
