<?php

/* 
 * Copyright (C) 2022 Sergey Ilyin <developer@ilyins.ru>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

include_once 'config.php';

spl_autoload_register(function ($class) {
    switch ($class) {

        default:
            include __DIR__."/classes/$class.class.php";
            break;
    }
});

$inputRequestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD');

switch ($inputRequestMethod){
    case 'GET':
        $inputRequestData = filter_input_array(INPUT_GET);
        break;
    case 'POST':
        $inputRequestData = filter_input_array(INPUT_POST);
        break;
}

for ($index = 101; $index < 128; $index++) {
    $host = "10.2.2.$index";
    $fp = fsockopen($host, 23, $errno, $errstr, 1);
    if ($fp) {
        $onus = BDCom::getLastDereg($host, 8);
        if (count($onus) > 0){
            $rows = '';
            foreach ($onus as $onu) {
                $cid = BGB::getCidByHostPort($host, "{$onu['port']}:{$onu['channel']}");
                $mapAddress = BGB::getSwitchAddress($host);
                $graphLink = "https://zbx.fialka.tv/d/d3FaolEMk/epon-interface?orgId=1&from=now-24h&to=now&var-Group=PON&var-Host={$mapAddress} ({$host})&var-port= EPON0/{$onu['port']}:{$onu['channel']}";
                $portLink = sprintf("<a target=_blank href='%s'>%s</a>", $graphLink, "{$onu['port']}:{$onu['channel']}");
                $techLink = "https://fialka.tv/tech/?cid=$cid";
                $cidLink = sprintf("<a target=_blank href='%s'>%s</a>", $techLink, $cid);
                $format = '<tr><td>%s</td><td>%s</td></tr>';
                $rows .= sprintf($format, $portLink, $cidLink);
            }
            echo "<table border='1'><caption>$host</caption><th>port</th><th>cid</th>$rows</table>";
        }
    } else {
        echo "Switch $host didn't answer</br>";
    }
    fclose($fp);
}
