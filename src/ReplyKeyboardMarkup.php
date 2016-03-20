<?php namespace korchasa\Telegram;

class ReplyKeyboardMarkup
{
    /**
     * Array of button rows, each represented by an Array of Strings
     *
     * @var string[][]
     */
    public $keyboard = [];

    /**
     * Requests clients to resize the keyboard vertically for optimal fit (e.g., make the
     * keyboard smaller if there are just two rows of buttons). Defaults to false, in which
     * case the custom keyboard is always of the same height as the app's standard keyboard.
     *
     * @var bool
     */
    public $resize_keyboard = true;

    /**
     * Requests clients to hide the keyboard as soon as it's been used.
     *
     * @var bool
     */
    public $one_time_keyboard = false;

    /**
     * Use this parameter if you want to show the keyboard to specific users only. Targets:
     * 1) users that are @mentioned in the text of the Message object;
     * 2) if the bot's message is a reply (has reply_to_message_id), sender of the
     * original message.
     *
     * Example: A user requests to change the bot‘s language, bot replies to the request
     * with a keyboard to select the new language. Other users in the group don’t see
     * the keyboard.
     *
     * @var bool
     */
    public $selective = false;

    public function __construct($data)
    {
        if ($data) {
            if (property_exists($data, 'keyboard')) {
                $this->keyboard = $data->keyboard;
            }
            if (property_exists($data, 'resize_keyboard')) {
                $this->resize_keyboard = $data->resize_keyboard;
            }
            if (property_exists($data, 'one_time_keyboard')) {
                $this->one_time_keyboard = $data->one_time_keyboard;
            }
        }
    }
}
