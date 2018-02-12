<?php

$transactpro_dir = dirname(__FILE__);

require_once $transactpro_dir . DIRECTORY_SEPARATOR . 'cron_functions.php';

if ($index = transactpro_init($transactpro_dir)) {
    require_once $index;
}