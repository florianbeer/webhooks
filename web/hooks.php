<?php

header('Content-type: text/plain');

require_once(__DIR__.'/../vendor/autoload.php');
require_once(__DIR__.'/../config.php');
require_once(__DIR__.'/core.php');

/**
 * Check with the GitHub API if the source IP matches one from GitHub.
 */

$match = false;

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/meta');
curl_setopt($ch, CURLOPT_USERAGENT, 'betacie/githooks');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$data = curl_exec($ch);
curl_close($ch);

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

    doTheHooks($name, $branch, $repositories[$name], $transport);

}