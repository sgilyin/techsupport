<?php
/*
Template Name: TechSupport
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
$inputRequestData = filter_input_array(INPUT_POST) ?? filter_input_array(INPUT_GET);

if (!$inputRequestData['cid']){
    echo HTML::getSearchForm(false, $device);
} else {
    if ($inputRequestData['btnDelKeaLease']) {
        $args['ip-address'] = $inputRequestData['btnDelKeaLease'];
        $delKeaLease = KEADHCP::exec('dhcp4', 'lease4-del', $args);
        if ($delKeaLease[0]->result == 0) {
            echo "<script language='javascript'>alert('IP-адрес {$inputRequestData['btnDelKeaLease']} удален из базы DHCP')</script>";
        }
    }
    $cid = $inputRequestData['cid'];
    echo HTML::getSearchForm($cid, $device);
    $contractData = BGB::getData($cid, 'contract')->fetch_object();
    $contractServices = BGB::getData($cid, 'services');

    $device = (preg_match('/Android|iPhone/', filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'))) ? 'Mobile' : 'PC';

    echo HTML::getContractInfo($contractData, $device);

    $services = new stdClass();

    while ($service = mysqli_fetch_object($contractServices)) {

        switch ($service->type) {
            case 'Gray-IP':
                $patterns = array('/Gray-IP-/', '/White-IP-/', '/[:-].*/');
                $replacements = '';
                $switch = preg_replace($patterns, $replacements, $service->switch);
                switch ($switch) {
                    case 'EdgeCore':
                        if ($inputRequestData['btnCableTest']){
                            EdgeCore::cableTest($service->host, preg_replace('/\D+/', '', $service->title));
                        }
                        if ($inputRequestData['btnShutdown']){
                            EdgeCore::changeIfAdminStatus($service->host, preg_replace('/\D+/', '', $service->title), 2);
                        }
                        if ($inputRequestData['btnNoShutdown']){
                            EdgeCore::changeIfAdminStatus($service->host, preg_replace('/\D+/', '', $service->title), 1);
                        }
                        echo HTML::getGraySwitchInfo($service->host, preg_replace('/\D+/', '', $service->title), $device, $switch);
                        break;
                    case 'SNR':
                        if ($inputRequestData['btnCableTest']){
                            SNR::cableTest($service->host, preg_replace('/\D+/', '', $service->title));
                        }
                        if ($inputRequestData['btnShutdown']){
                            SNR::changeIfAdminStatus($service->host, preg_replace('/\D+/', '', $service->title), 0);
                        }
                        if ($inputRequestData['btnNoShutdown']){
                            SNR::changeIfAdminStatus($service->host, preg_replace('/\D+/', '', $service->title), 1);
                        }
                        echo HTML::getGraySwitchInfo($service->host, preg_replace('/\D+/', '', $service->title), $device, $switch);
                        break;

                    default:
                        echo 'Неизвестный тип устройства';
                        break;
                }
                break;

            case 'White-IP':
                echo HTML::getWhiteSwitchInfo($service->host, $service->title, $device);
                break;

            case 'GePON':
                echo HTML::getPonSwitchInfo($service->host, $service->title, $device);
                break;

            case 'Sector-Wireless':
                echo HTML::getWirelessSwitchInfo($service->host, $service->title, $device);
                break;

            default:
                echo '<p><mark>Обработчик отсутствует</mark></p>';
                break;
        }
        $i++;
        $services->{$i} = $service;
    }
    $services->count = $i;

    echo HTML::getBitrixForm($contractData, $device);

    if ($inputRequestData['bx']['address'] && $inputRequestData['bx']['type'] && $inputRequestData['bx']['halfDay'] && $inputRequestData['bx']['date']){
        $bx = BX24::createTask($inputRequestData['cid'], $inputRequestData['bx'], $contractData, $services);
        if ($bx->result->task->id){
            echo '<script language="javascript">alert("Задача в Битрикс создана")</script>';
        }
    }
}
#get_footer();