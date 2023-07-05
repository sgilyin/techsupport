<?php

/**
 * Class for KEADHCP
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class KEADHCP {
    private static function getHeaders($data) {
        return array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        );
    }

    public static function exec($service, $command, $arguments) {
        $post['service'] = array($service);
        $post['command'] = $command;
        $post['arguments'] = $arguments;
        $data = json_encode($post);
        $headers = self::getHeaders($data);
        return cURL::executeRequest(KEA_DHCP_HOST, $data, $headers);
    }

    public static function lease4GetByHWAddress($hwAddress) {
        $arguments['hw-address'] = $hwAddress;
        return self::exec('dhcp4', 'lease4-get-by-hw-address', $arguments);
    }
}
