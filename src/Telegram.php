<?php namespace korchasa\Telegram;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use InvalidArgumentException;
use korchasa\Telegram\Structs\Message;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Webmozart\Assert\Assert;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use korchasa\Telegram\Structs\Payload\AbstractPayload;
use korchasa\Telegram\Structs\Update;
use korchasa\Telegram\Structs\Chat;

class Telegram
{
    /** @var Client */
    protected $client;
    /** @var Message */
    public $last_message;
    /** @var string */
    public $token;
    /** @var Chat */
    public $current_chat;

    const TYPING = 'typing';
    const UPLOAD_PHOTO = 'upload_photo';
    const RECORD_VIDEO = 'record_video';
    const UPLOAD_VIDEO = 'upload_video';
    const RECORD_AUDIO = 'record_audio';
    const UPLOAD_AUDIO = 'upload_audio';
    const UPLOAD_DOCUMENT = 'upload_document';
    const FIND_LOCATION = 'find_location';

    /**
     * Telegram constructor.
     *
     * @param       $token
     * @param array $guzzle_options
     * @param null  $log_file
     */
    public function __construct($token, $log_file = null, array $guzzle_options = [])
    {
        if (!$token) {
            throw new InvalidArgumentException('Param #0 must be a telegram bot token');
        }

        $this->token = $token;

        $guzzle_options = array_merge([
            'base_uri'    => 'https://api.telegram.org/bot'.$token.'/',
            'http_errors' => true,
        ], $guzzle_options);

        if ($log_file) {
            $this->client = $this->createLoggable($log_file, $guzzle_options);
        } else {
            $this->client = new Client($guzzle_options);
        }
    }

    public function setWebhook($url)
    {
        return $this->request('setWebhook', [ 'url' => $url ]);
    }

    /**
     * @param integer|null $offset
     * @param integer|null $limit
     * @param int  $timeout
     *
     * @return Update[]
     */
    public function getUpdates(int $offset = null, int $limit = null, int $timeout = 10)
    {
        $response = $this->request(
            'getUpdates',
            [
                'offset'  => $offset,
                'limit'   => $limit,
                'timeout' => $timeout,
            ]
        );

        $updates = [];
        foreach ($response->result as $update_data) {
            $updates[] = new Update(new Unstructured($update_data));
        }

        return $updates;
    }

    public function loop($update_handler, $iterations = null)
    {
        if (!is_callable($update_handler)) {
            throw new InvalidArgumentException('Param #0 must be a callable');
        }

        $update = (object) [
            'update_id' => 0
        ];

        while (0 !== $iterations--) {
            foreach ($this->getUpdates($update->update_id + 1) as $update) {
                $update->telegram = $this;
                $update_handler($update);
            }
        }
    }


    /**
     * @param Chat|integer    $receiver
     * @param string  $text
     * @param AbstractPayload $reply_markup
     * @param integer $reply_to_message_id
     * @param bool    $disable_web_page_preview
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function sendMessage(
        $chatOrUser,
        $text,
        AbstractPayload $reply_markup = null,
        $reply_to_message_id = null,
        $disable_web_page_preview = false,
        $parse_mode = 'html',
        $disable_notifications = true
    ) {
        Assert::notNull($chatOrUser);
        $this->last_message = new Message(new Unstructured(['text' => $text]));
        try {
            $params = [
                'chat_id'                  => is_object($chatOrUser) ? $chatOrUser->id : $chatOrUser,
                'text'                     => $text,
                'disable_web_page_preview' => $disable_web_page_preview,
                'reply_to_message_id'      => $reply_to_message_id,
                'parse_mode'               => $parse_mode,
                'disable_notifications'    => $disable_notifications,
            ];

            if ($reply_markup) {
                $params['reply_markup'] = $reply_markup->export();
            }

            return $this->request('sendMessage', $params);
        } catch (ClientException $e) {
            if (403 === $e->getResponse()->getStatusCode()) {
                return null;
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param Chat $chat
     * @param $latitude
     * @param $longitude
     * @param AbstractPayload $reply_markup
     * @param integer $reply_to_message_id
     * @return mixed
     */
    public function sendLocation(
        Chat $chat,
        $latitude,
        $longitude,
        $reply_markup = null,
        $reply_to_message_id = null
    ) {
        try {
            return $this->request(
                'sendLocation',
                [
                    'chat_id'             => $chat->id,
                    'latitude'            => $latitude,
                    'longitude'           => $longitude,
                    'reply_to_message_id' => $reply_to_message_id,
                    'reply_markup'        => json_encode($reply_markup),
                ]
            );
        } catch (ClientException $e) {
            if (403 === $e->getResponse()->getStatusCode()) {
                return null;
            } else {
                throw $e;
            }
        }
    }

    public function sendTypingAction(Chat $chat)
    {
        return $this->sendChatAction($chat, self::TYPING);
    }

    /**
     * @param Chat $chat
     * @param      $action
     *
     * @return mixed
     */
    public function sendChatAction(Chat $chat, $action)
    {
        return $this->request(
            'sendChatAction',
            [
                'chat_id' => $chat->id,
                'action'  => $action,
            ]
        );
    }

    public function sendAnswerForCallbackQuery(
        $callback_query_id,
        $text,
        $show_alert = false,
        $url = null,
        $cache_time = 0
    ) {
        return $this->request(
            'answerCallbackQuery',
            [
                'callback_query_id' => $callback_query_id,
                'text'              => $text,
                'show_alert'        => $show_alert,
                'url'               => $url,
                'cache_time'        => $cache_time
            ]
        );
    }

    public function request($uri, array $params = [], array $options = [])
    {
        return $this->sendByPostFormParams($uri, $params, $options);
    }

    /**
     * @param       $log_file
     * @param array $guzzle_options
     *
     * @return Client
     * @throws \Exception
     */
    protected function createLoggable($log_file, array $guzzle_options = [])
    {
        $writer = new StreamHandler($log_file);
        $writer->setFormatter(new LineFormatter(null, null, true));
        $logger = new Logger('korchasa\Telegram');
        $logger->pushHandler($writer);

        $handler = HandlerStack::create();
        $handler->push(
            Middleware::log(
                $logger,
                new MessageFormatter(MessageFormatter::DEBUG)
            )
        );

        return new Client(array_merge(array(
            'handler' => $handler,
            'http_errors' => false,
        ), $guzzle_options));
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    protected function sendByPostFormParams($uri, array $params = [], array $options = [])
    {
        return json_decode(
            $this->client->post(
                $uri,
                array_merge(
                    $options,
                    [
                        'form_params' => $params,
                    ]
                )
            )->getBody()
        );
    }

    /**
     * @deprecated Use sendByPostFormParams()
     *
     * @param       $uri
     * @param array $params
     * @param array $options
     *
     * @return mixed
     */
    protected function sendByPostWithJsonInBody($uri, array $params = [], array $options = [])
    {
        return json_decode(
            $this->client->post(
                $uri,
                array_merge(
                    $options,
                    [
                        'json' => $params,
                    ]
                )
            )->getBody()
        );
    }
}
