<?php namespace korchasa\Telegram\Structs\Payload;

abstract class AbstractPayload
{
    public function export()
    {
        $array = json_decode(json_encode($this), true);
        return $this->arrayFilterNulls($array);
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
