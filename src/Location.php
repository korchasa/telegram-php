<?php namespace korchasa\Telegram;

class Location
{
    /** @var float $latitude */
    public $latitude;
    /** @var float $longitude */
    public $longitude;

    public function __construct($data)
    {
        if ($data) {
            $this->latitude = $data->latitude;
            $this->longitude = $data->longitude;
        }
    }
}
