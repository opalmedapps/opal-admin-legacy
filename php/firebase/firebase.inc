<?php

    $pathname 	= __DIR__;
    $abspath 	= str_replace('firebase', '', $pathname);

    include_once($abspath . 'config.php');
    require $abspath . 'vendor/autoload.php';

?>