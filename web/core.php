<?php

/**
 * @param string $ip   IP address to test
 * @param string $cidr CIDR mask to match with
 *
 * @return bool
 */
function ipCIDRCheck($ip, $cidr)
{

    list ($net, $mask) = explode('/', $cidr);

    $ipNet = ip2long($net);
    $ipMask = ~((1 << (32 - $mask)) - 1);

    $ipIp = ip2long($ip);

    $ipIpNet = $ipIp & $ipMask;

    return ($ipIpNet == $ipNet);

}

function doTheHooks($name, $branch, $repo, $transport)
{

    $result = null;

    chdir($repo['dir']);

    ob_start();

    echo "~> git pull origin ".$branch." 3>&1\n";
    system(__ENV_PATH__.' '.__GIT_PATH__.' pull origin '.$branch.' 2>&1');
    echo "\n";

    if (file_exists($repo['dir'].'/'.__HOOKS_FILE__)) {

        $yaml = Spyc::YAMLLoad($repo['dir'].'/'.__HOOKS_FILE__);
        $cmds = array();

        if (is_array($yaml[$repo['branch']])) {

            $cmds = $yaml[$repo['branch']];

        } elseif (is_array($yaml['all'])) {

            $cmds = $yaml['all'];

        }

        foreach ($cmds as $cmd) {

            echo "~> ".$cmd."\n";
            system(__ENV_PATH__.' '.$cmd);
            echo "\n";

        }

        $result = ob_get_contents();

        if (is_array($yaml['emails'])) {

            foreach ($yaml['emails'] as $email) {

                $mailer = Swift_Mailer::newInstance($transport);

                $message = Swift_Message::newInstance()
                           ->setSubject(sprintf(__MAIL_SUBJECT__, $name))
                           ->setFrom(array(__MAIL_FROM_ADDRESS__ => __MAIL_FROM__))
                           ->setTo(array($email))
                           ->setBody($result)
                ;

                $result = $mailer->send($message);

            }

        }

    }


    ob_end_clean();

}
