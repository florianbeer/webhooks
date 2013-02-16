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