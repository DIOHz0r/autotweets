<?php


namespace App\Closure;


class Closure
{

    /**
     * @return \Closure
     */
    public static function trimValue(): \Closure
    {
        return function ($value) {
            return $value ? trim($value) : null;
        };
    }

    /**
     * @return \Closure
     */
    public static function notBlank(): \Closure
    {
        return function ($value) {
            if (false === $value || (empty($value) && '0' != $value)) {
                throw new \RuntimeException('This value should not be blank.');
            }
            return $value;
        };
    }

    /**
     * @return \Closure
     */
    public static function checkChoices(): \Closure
    {
        return function ($value) {
            if ($value != 1 && $value != 0) {
                return null;
            }
            return $value;
        };
    }
}