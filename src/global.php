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
            return \Myerscode\Config\Config::make()->get($key);
        }

        return \Myerscode\Config\Config::make()->all();
    }
}
