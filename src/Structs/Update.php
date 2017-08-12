<?php namespace korchasa\Telegram\Structs;

use korchasa\Telegram\Unstructured;
use korchasa\Telegram\Telegram;
use korchasa\Telegram\Structs\Payload\AbstractPayload;

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

    /**
     * @var Telegram|null
     */
    public $telegram;

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
        if (!$this->message && !$this->edited_message) {
            return false;
        }

        return (bool) $this->message()->text;
    }

    public function isReply()
    {
        return (bool) $this->message && $this->message->reply_to_message;
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
        } elseif ($this->edited_message) {
            return $this->edited_message;
        }

        throw new \LogicException("Can't find any message in update");
    }

    public function replyMessage(
        $text,
        AbstractPayload $reply_markup = null,
        $reply_to_message_id = null,
        $disable_web_page_preview = false,
        $parse_mode = 'html'
    ) {
        return $this->telegram->sendMessage(
            $this->chat(),
            $text,
            $reply_markup,
            $reply_to_message_id,
            $disable_web_page_preview,
            $parse_mode,
            true
        );
    }

    public function replyLocation(
        $latitude,
        $longitude,
        $reply_markup = null,
        $reply_to_message_id = null
    ) {
        return $this->telegram->sendLocation(
            $this->chat(),
            $latitude,
            $longitude,
            $reply_markup,
            $reply_to_message_id
        );
    }

    public function replyCallbackQuery(
        $text,
        $show_alert = false,
        $url = null,
        $cache_time = 0
    ) {
        if (!$this->callback_query) {
            throw new \LogicException("Update not a callbackQuery");
        }

        return $this->telegram->sendAnswerForCallbackQuery(
            $this->callback_query->id,
            $text,
            $show_alert,
            $url,
            $cache_time
        );
    }
}
