<?php

call_user_func_array(function ($rootDir) {
    $autoload = "$rootDir/vendor/autoload.php";
    if (file_exists($autoload)) {
        require_once $autoload;
    }
}, array(dirname(dirname(dirname(__DIR__)))));
