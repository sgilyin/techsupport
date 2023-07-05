<?php

/*
Template Name: SwitchSubscribers
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
$device = (preg_match('/Android|iPhone/', filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'))) ? 'Mobile' : 'PC';

echo <<<HTML
<div class='entry'>
    <div class='woo-sc-box normal rounded full'>
        <form method=post>
            Host: <input type=text id=host name=host value=$inputRequestData[host]>
            <input type=submit value=Show>
        </form>
    </div>
</div>
HTML;

if ($inputRequestData['host']) {
    $query = "
SELECT DISTINCT idps15.port, IF((t_cpt2.flat='' OR t_cpt2.flat IS NULL),'ЮЛ',t_cpt2.flat) flat
FROM inv_device_15 id15
LEFT JOIN inv_device_port_subscription_15 idps15 ON (idps15.deviceId=id15.id)
LEFT JOIN inet_serv_15 is15 ON (idps15.subscriberId=is15.id)
LEFT JOIN contract_parameter_type_2 AS t_cpt2 ON (t_cpt2.cid=is15.contractId) 
WHERE id15.host='{$inputRequestData['host']}' AND idps15.dateTo IS NULL
";
    $bgbQuery = BGB::sql($query);
    $rows = '';
    $format = '<tr><td>%s</td><td>%s</td></tr>';
    while ($row = $bgbQuery->fetch_object()) {
        $rows .= sprintf($format, $row->port, $row->flat);
    }
    $patterns = array('/{HOST}/', '/{ROWS}/');
    $replacements = array($inputRequestData['host'], $rows);
    echo preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
        "/templates/SwitchPortSubscribers.tpl"));
}
