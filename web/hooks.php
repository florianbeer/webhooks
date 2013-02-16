<?php

header('Content-type: text/plain');

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/../config.php');
require_once(__DIR__.'/core.php');

/**
 * Check with the GitHub API if the source IP matches one from GitHub.
 */

$match = false;

$data = file_get_contents('https://api.github.com/meta');
$metaJSON = json_decode($data);

foreach ($metaJSON->hooks as $cidr) {

    if (ipCIDRCheck($_SERVER['REMOTE_ADDR'], $cidr)) {

        $match = true;
        break;

    }

}

if (!$match || !isset($_POST['payload'])) {

    die;

}

/**
 * Decode the JSON Payload.
 */

$json = json_decode($_POST['payload']);

$refs = explode('/', $json->ref);
$branch = $refs[count($refs)-1];
$name = $json->repository->owner->name.'/'.$json->repository->name.'/'.$branch;

/**
 * If the repo is configured, do the dance.
 */

if (is_array($repositories[$name])) {

    $repo = $repositories[$name];
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