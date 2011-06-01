<?php

require_once 'MMMConfiguration.class.php';
require_once 'Net/DNS2.php';

$config_manager = MMMConfiguration::getInstance();
if (!$config_manager->load('mmm_ddns_agent.conf')) {
    echo 'Invalid configuration for mmm_ddns_agent';
    exit(1);
}

// a forking agent to listen for commands to pass to nameserver
// to be used with MMM
$sock = socket_create_listen($config_manager->item('port'));

# loop
if ($sock)
{
    print("Daemon started, listening on ".$config_manager->item('port')."\n");

    while ($new_socket = socket_accept($sock))
    {
        // fork so child handles connection
        $pid = pcntl_fork();
        if ($pid==-1)
        {
            print("ERROR: fork failed on receiving command, quitting\n");
            exit(1);
        }
        elseif ($pid)
        {
            // parent, close new socket and keep listening
            socket_close($new_socket);

            // clear any previously exited children that are still zombie
            while( pcntl_waitpid(-1, $status, WNOHANG) > 0 )
            {
            }
        }
        else
        {
            // child, let's handle the socket command
            $cmd = socket_read($new_socket,4096,PHP_NORMAL_READ);
            $cmd = trim($cmd);
            print("Command Received: $cmd \n");
            $cmd = explode(':',$cmd);

            switch ($cmd[0])
            {
                case 'ADDIP':
                    AddIp($cmd[1],$cmd[2]);
                    break;
                case 'CLEARIP':
                    ClearIp($cmd[1],$cmd[2]);
                    break;
                default:
                    print("ERROR: Command not recognized \n");
            }
            // close socket we're done processing
            socket_close($new_socket);
            // and exit so child terminates
            exit(0);
        }
    }
    // close master
    print("Done");
    socket_close($sock);
}
else
{
    print("ERROR: Failed initializing daemon\n");
}

function AddIp($hostname,$ip)
{
    RemoveHostFromDnsServer($hostname);
    AddHostAndIpToDnsServer($hostname,$ip);
}

function ClearIp($hostname,$ip)
{
    RemoveHostFromDnsServer($hostname);
}

function AddHostAndIpToDnsServer($hostname,$ip)
{
    $config_manager = MMMConfiguration::getInstance();

    $updater = BuildUpdater('register');
    $ttl = $config_manager->item('ttl');
    $type = $config_manager->item('type');

    print("Executing: nsupdate add $hostname $ttl $type $ip'\n");
    $add_record = Net_DNS2_RR::fromString("$hostname $ttl IN $type $ip");
    $updater->add($add_record);

    return Sendupdate($updater);
}

function RemoveHostFromDnsServer($hostname)
{
    $config_manager = MMMConfiguration::getInstance();

    $updater = BuildUpdater('remove');
    $type = $config_manager->item('type');

    print("Executing: nsupdate delete '$hostname $type'\n");
    $updater->deleteAny($hostname, $type);

    return Sendupdate($updater);
}

function BuildUpdater($action='remove')
{
    $config_manager = MMMConfiguration::getInstance();

    $dns_server = $config_manager->item('dns_server');
    $dns_port = $config_manager->item('dns_port');
    $zone = $config_manager->item('zone');
    $updater = new Net_DNS2_Updater($zone, array('nameservers' => array($dns_server), 'dns_port' => $dns_port));

    return $updater;
}

function SendUpdate($updater) {
    SignedPacketByTSIG($upater);

    try {
        $updater->update();

        return true;
    } catch (Exception $e) {
        echo "Failed to update: ", $e->getMessage(), "\n";
        return false;
    }
}

function SignedPacketByTSIG($updater) {
    $config_manager = MMMConfiguration::getInstance();

    $tsig_key_name = $config_manager->item('tsig_key_name');
    $tsig_key_value = $config_manager->item('tsig_key_value');

    if (!is_null($tsig_key_name) && !is_null($tsig_key_value)) {
        $updater->signTSIG($tsig_key_name, $tsig_key_value);
    }
}
