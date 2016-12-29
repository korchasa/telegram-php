<?php namespace korchasa\Telegram;

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
     * @param null $offset
     * @param null $limit
     * @param int  $timeout
     *
     * @return Update[]
     */
    public function getUpdates($offset = null, $limit = null, $timeout = 10)
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

        while(0 !== $iterations--) {
            foreach ($this->getUpdates($update->update_id + 1) as $update) {
                $update_handler($this, $update);
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
        $chat,
        $text,
        AbstractPayload $reply_markup = null,
        $reply_to_message_id = null,
        $disable_web_page_preview = false,
        $parse_mode = 'html'
    ) {
        $this->last_message = $text;
        try {
            $params = [
                'chat_id'                  => is_object($chat) ? $chat->id : $chat,
                'text'                     => $text,
                'disable_web_page_preview' => $disable_web_page_preview,
                'reply_to_message_id'      => $reply_to_message_id,
                'parse_mode'               => $parse_mode
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
     * @param User                                                  $receiver
     * @param                                                       $latitude
     * @param                                                       $longitude
     * @param AbstractPayload                                       $reply_markup
     * @param integer                                               $reply_to_message_id
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\ClientException
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

    public function request($uri, array $params = [], array $options = [])
    {
        return $this->sendByPostWithJsonInBody($uri, $params, $options);
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
        $writer = new \Monolog\Handler\StreamHandler($log_file);
        $writer->setFormatter(new \Monolog\Formatter\LineFormatter(null, null, true));
        $logger = new \Monolog\Logger('korchasa\Telegram');
        $logger->pushHandler($writer);

        $handler = \GuzzleHttp\HandlerStack::create();
        $handler->push(\GuzzleHttp\Middleware::log(
            $logger,
            new \GuzzleHttp\MessageFormatter(\GuzzleHttp\MessageFormatter::DEBUG)
        ));

        return new Client(array_merge(array(
            'handler' => $handler,
            'http_errors' => false,
        ), $guzzle_options));
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
