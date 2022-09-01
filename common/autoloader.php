<?php

/**
 * Name: Authloader by Prk PHP Framework
 * Author: Prk
 * Website: https://imprk.me/
 * Date: 2018-01-42
 * Location: Guangzhou Guangdong, People's Republic of China
 * Update: https://github.com/BiliPrk/Prk-PHP-Framework
 */

class Autoloader {

    public static function register() {
        spl_autoload_register(
            [
                new self,
                'autoload'
            ]
        );
    }

    public static function autoload($className) {
        $filePath = __DIR__ . DIRECTORY_SEPARATOR . $className;
        $filePath = str_replace('\\', DIRECTORY_SEPARATOR, $filePath) . '.php';
        if (file_exists($filePath)) {
            require_once $filePath;
            return true;
        } else return false;
    }

}
