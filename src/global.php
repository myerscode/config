<?php

if (!function_exists('config')) {
    /**
     * @param  string|null  $key
     *
     * @return mixed
     */
    function config(string $key = null): mixed
    {
        if ($key) {
            return \Myerscode\Config\Config::make()->value($key);
        }

        return \Myerscode\Config\Config::make()->values();
    }
}
