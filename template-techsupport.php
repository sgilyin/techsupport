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
$inputRequestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

switch ($inputRequestMethod){
    case 'GET':
        $inputRequestData = filter_input_array(INPUT_GET);
        break;
    case 'POST':
        $inputRequestData = filter_input_array(INPUT_POST);
        break;
}

if (!$inputRequestData['cid']){
    echo HTML::getIdForm(false);
} else {
    $cid = $inputRequestData['cid'];
    echo HTML::getIdForm($cid);
    $bgb_result = BGB::getData($cid);

    if ($bgb_result->host && $bgb_result->port){
        if ($inputRequestData['btnCableTest']){
            EdgeCore::cableTest($bgb_result->host, $bgb_result->port);
        }

        $edgeCoreData = EdgeCore::getData($bgb_result->host, $bgb_result->port);

        echo HTML::getContractInfo($bgb_result);
        echo HTML::getSwitchInfo($bgb_result, $edgeCoreData);
        echo HTML::getPortInfo($bgb_result, $edgeCoreData);
        echo HTML::getCableTestInfo($edgeCoreData);
        echo HTML::getSwitchLog($bgb_result->host);
        echo HTML::getBitrixForm($bgb_result);
        if ($inputRequestData['bx']['address'] && $inputRequestData['bx']['phone'] && $inputRequestData['bx']['type'] && $inputRequestData['bx']['halfDay'] && $inputRequestData['bx']['date']){
            $bx = BX24::createTask($inputRequestData['cid'], $inputRequestData['bx'], $bgb_result, $edgeCoreData);
            if ($bx->result->task->id){
                echo '<script language="javascript">alert("Задача в Битрикс создана")</script>';
            }
        }
    } else {
        echo '<p>Договор не подключен</p>'.$htmlEmptyForm;
    }
}
get_footer();
