<?php namespace korchasa\Telegram;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Telegram
{
    /**
     * @var Client
     */
    protected $client;
    public $last_message;
    public $token;

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
    public function __construct($token, array $guzzle_options = [], $log_file = null)
    {
        $this->token = $token;

        $guzzle_options = array_merge([
            'base_uri'    => 'https://api.telegram.org/bot'.$token.'/',
            'http_errors' => true,
        ], $guzzle_options);

        if ($log_file && class_exists('\Monolog\Logger')) {
            $this->client = $this->createLoggable($log_file, $guzzle_options);
        } else {
            $this->client = new Client($guzzle_options);
        }
    }

    public function setWebhook($url)
    {
        return $this->request(
            'setWebhook',
            [
                'url' => $url,
            ]
        );
    }

    /**
     * @param null $offset
     * @param null $limit
     * @param int  $timeout
     *
     * @return Update[]
     */
    public function getUpdates($offset = null, $limit = null, $timeout = 0)
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
            $updates[] = new Update($update_data);
        }

        return $updates;
    }


    /**
     * @param User                                                  $receiver
     * @param string                                                $text
     * @param ReplyKeyboardMarkup|ReplyKeyboardHide|ForceReply|null $reply_markup
     * @param integer                                               $reply_to_message_id
     * @param bool|false                                            $disable_web_page_preview
     *
     * @return mixed|null
     */
    public function sendMessage(
        User $receiver,
        $text,
        $reply_markup = null,
        $reply_to_message_id = null,
        $disable_web_page_preview = false
    ) {
        $this->last_message = $text;
        try {
            return $this->request(
                'sendMessage',
                [
                    'chat_id'                  => $receiver->user_id,
                    'text'                     => $text,
                    'disable_web_page_preview' => $disable_web_page_preview,
                    'reply_to_message_id'      => $reply_to_message_id,
                    'reply_markup'             => json_encode($reply_markup),
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

    /**
     * @param User                                                  $receiver
     * @param                                                       $latitude
     * @param                                                       $longitude
     * @param ReplyKeyboardMarkup|ReplyKeyboardHide|ForceReply|null $reply_markup
     * @param integer                                               $reply_to_message_id
     *
     * @return mixed|null
     */
    public function sendLocation(
        User $receiver,
        $latitude,
        $longitude,
        $reply_markup = null,
        $reply_to_message_id = null
    ) {
        try {
            return $this->request(
                'sendLocation',
                [
                    'chat_id'             => $receiver->user_id,
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

    public function sendTypingAction(User $receiver)
    {
        return $this->sendChatAction($receiver, self::TYPING);
    }

    /**
     * @param User $receiver
     * @param      $action
     *
     * @return mixed
     */
    public function sendChatAction(User $receiver, $action)
    {
        return $this->request(
            'sendChatAction',
            [
                'chat_id' => $receiver->user_id,
                'action'  => $action,
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

    protected function sendByGet($uri, array $params = [], array $options = [])
    {
        return json_decode(
            $this->client->get(
                $uri,
                array_merge(
                    $options,
                    [
                        'query' => $params,
                    ]
                )
            )->getBody()
        );
    }

    protected function sendByPostMultipart($uri, array $params = [], array $options = [])
    {
        $multipart_params = [];
        foreach ($params as $key => $value) {
            $multipart_params[] = [
                'name'     => $key,
                'contents' => !is_object($value) ? (string)$value : json_encode($value),
            ];
        }

        return json_decode(
            $this->client->post(
                $uri,
                array_merge(
                    $options,
                    [
                        'multipart' => $multipart_params,
                    ]
                )
            )->getBody()
        );
    }
}
