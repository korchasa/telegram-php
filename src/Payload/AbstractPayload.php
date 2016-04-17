<?php namespace korchasa\Telegram\Payload;

abstract class AbstractPayload
{
    public function json()
    {
        $array = json_decode(json_encode($this), true);
        return json_encode($this->arrayFilterNulls($array));
    }

    protected function arrayFilterNulls($array)
    {
        foreach ($array as $key => $value) {
            if (null === $value) {
                unset($array[$key]);
            } elseif (is_array($value)) {
                $array[$key] = $this->arrayFilterNulls($value);
            }
        }

        return $array;
    }
}
