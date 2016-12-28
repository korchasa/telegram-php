<?php namespace korchasa\Telegram;

class Unstructured
{
    protected $payload;

    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    public function scalar($key, $default = null)
    {
        return $this->get($this->payload, $key, $default);
    }

    function object($key, $class)
    {
        if ($constructor_data = $this->get($this->payload, $key)) {
            return new $class(new Unstructured($constructor_data));
        }
    }

    /**
     * Get an item from an object or array using "dot" notation.
     *
     * @param  object|array $object_or_array
     * @param  string       $key
     * @param  mixed        $default
     *
     * @return mixed
     */
    protected function get($object_or_array, $key, $default = null)
    {
        if (is_array($object_or_array)) {
            if (is_null($key)) {
                return $object_or_array;
            }

            if (isset($object_or_array[$key])) {
                return $object_or_array[$key];
            }

            foreach (explode('.', $key) as $segment) {
                if (!is_array($object_or_array) || !array_key_exists($segment, $object_or_array)) {
                    return value($default);
                }

                $object_or_array = $object_or_array[$segment];
            }

            return $object_or_array;
        } else {
            if (is_null($key) || trim($key) == '') {
                return $object_or_array;
            }

            foreach (explode('.', $key) as $segment) {
                if (!is_object($object_or_array) || !isset($object_or_array->{$segment})) {
                    return value($default);
                }

                $object_or_array = $object_or_array->{$segment};
            }

            return $object_or_array;
        }
    }
}