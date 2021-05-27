<?php

/*
Template Name: CableTest
Template Post Type: page
*/

/**
 * @author Sergey Ilyin <developer@ilyins.ru>
 */

include_once 'config.php';

spl_autoload_register(function ($class) {
    switch ($class) {
        case 'Woocommerce':
            break;

        default:
            include __DIR__."/classes/$class.class.php";
            break;
    }
});

get_header();
$inputRequestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

switch ($inputRequestMethod){
    case 'GET':
        $inputRequestData = filter_input_array(INPUT_GET);
        break;
    case 'POST':
        $inputRequestData = filter_input_array(INPUT_POST);
        break;
}

$device = (preg_match('/Android|iPhone/', filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'))) ? 'Mobile' : 'PC';

echo <<<HTML
<div class='entry'>
    <div class='woo-sc-box normal rounded full'>
        <form method=post>
            Host: <input type=text id=host name=host value=$inputRequestData[host]>
            Port: <input type=text id=port name=port value=$inputRequestData[port]>
            <input type=submit value=Test>
        </form>
    </div>
</div>
HTML;

if ($inputRequestData['host'] && $inputRequestData['port']) {
    EdgeCore::cableTest($inputRequestData['host'], $inputRequestData['port']);
    echo HTML::getGraySwitchInfo($inputRequestData['host'], $inputRequestData['port'], $device);
}
