<?php

define('BASE_PATH', implode(array_slice(explode(DIRECTORY_SEPARATOR, __FILE__), 0, -2), DIRECTORY_SEPARATOR));
require_once BASE_PATH . DIRECTORY_SEPARATOR . 'MMMConfiguration.class.php';

$delete_record = "CLEARIP:db-writer1.nsupdate.example.com:db-001.nsupdate.example.com\n";
$register_record = "ADDIP:db-writer1.nsupdate.example.com:db-001.nsupdate.example.com\n";
$config_manager = MMMConfiguration::getInstance();
if (!$config_manager->load(BASE_PATH . DIRECTORY_SEPARATOR . 'mmm_ddns_agent.conf')) {
    echo 'Invalid configuration for mmm_ddns_agent';
    exit(1);
}
var_dump($config_manager);

// entroy point
$options = getopt("dr");

// Send data
$sock = fsockopen('127.0.0.1', $config_manager->item('port'), $errno, $errmsg, 30);

if (!$sock) {
    echo "ERROR: $errno - $errmsg\n";
    exit(1);
}

// Delete record
if (isset($options['d'])) {
    echo "Delete $delete_record\n";
    fputs($sock, $delete_record);
}

// Register record
if (isset($options['r'])) {
    echo "Register $register_record\n";
    fputs($sock, $register_record);
}

fclose($sock);
