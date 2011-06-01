<?php

define('BASE_PATH', implode(array_slice(explode(DIRECTORY_SEPARATOR, __FILE__), 0, -2), DIRECTORY_SEPARATOR));
require_once BASE_PATH . DIRECTORY_SEPARATOR . 'MMMConfiguration.class.php';

$config_manager = MMMConfiguration::getInstance();
if (!$config_manager->load(BASE_PATH . DIRECTORY_SEPARATOR . 'mmm_ddns_agent.conf')) {
    echo 'Invalid configuration for mmm_ddns_agent';
    exit(1);
}
var_dump($config_manager);

// a forking agent to listen for commands to pass to nameserver
// to be used with MMM
$sock = socket_create_listen($config_manager->item('port'));
