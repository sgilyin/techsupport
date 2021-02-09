<?php

/*
 * Copyright (C) 2020 Sergey Ilyin <developer@ilyins.ru>
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

/**
 * Class for HTML
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class HTML {
    public static function getSearchForm($cid, $device) {
        $patterns = array('/{CID}/');

        $replacements = array($cid);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}SearchForm.tpl"));
    }

    public static function getContractInfo($contractData, $device) {
        $status = ($contractData->status == 'Активен') ? $contractData->status :
                "<font color='red'><b>$contractData->status</b></font>";

        $blockDays = static::getCountDays($contractData->tariff, $contractData->balance);

        $patterns = array('/{ABONENT}/', '/{ADDRESS}/', '/{STATUS}/',
            '/{TARIFF}/', '/{BALANCE}/', '/{BLOCK_DAYS}/', '/{COMMENT}/');

        $replacements = array($contractData->abonent, $contractData->address,
            $status, $contractData->tariff, $contractData->balance, $blockDays,
            $contractData->comment);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}ContractInfo.tpl"));
    }

    public static function getGraySwitchInfo($host, $port, $device) {
        $edgeCoreData = EdgeCore::getData($host, $port);
        $ifOperStatus = ($edgeCoreData->ifOperStatus == 1)? 'Up' : '<font color="red"><b>Down</b></font>';
        $ifAdminStatus = ($edgeCoreData->ifAdminStatus == 2)? '. <font color="red"><b>Shutdown!</b></font>' : '';
        $switchLast = BGB::getLastWorker($host)->fetch_object();

        switch ($edgeCoreData->ifAdminStatus) {
            case 2:
                $btnChangeIfAdminStatus = "<input type='submit' name='btnNoShutdown' value='No Shu'>";
                break;

            default:
                $btnChangeIfAdminStatus = "<input type='submit' name='btnShutdown' value='Shu'>";
                break;
        }

        if (isset($edgeCoreData->dhcpSnoopBinPort)) {
            for ($i = 0; $i < count($edgeCoreData->dhcpSnoopBinPort); $i++) {
                $rows .= "<tr><td>" . $edgeCoreData->dhcpSnoopBinPort[$i]['vlan'] . "</td>
                    <td>" . $edgeCoreData->dhcpSnoopBinPort[$i]['IpAddress'] . "</td>
                    <td>" . gmdate("H:i:s", $edgeCoreData->dhcpSnoopBinPort[$i]['LeaseTime']) . "</td>
                    <td>" . $edgeCoreData->dhcpSnoopBinPort[$i]['mac'] . "</td>
                    <td>" . static::getMacVendor($edgeCoreData->dhcpSnoopBinPort[$i]['mac']) . "</td></tr>";
            }
        }

        $patterns = array('/{HOST}/', '/{BTN_CHANGE_IF_ADMIN_STATUS}/',
            '/{SYS_UP_TIME}/', '/{PORT}/', '/{IF_LAST_CHANGE}/', '/{IF_OPER_STATUS}/',
            '/{IF_ADMIN_STATUS}/', '/{PORT_SPEED_DPX_STATUS}/', '/{PORT_OUT_UTIL}/',
            '/{PORT_IN_UTIL}/', '/{CABLE_DIAG_RESULT_TIME}/', '/{CABLE_A_STATUS}/',
            '/{CABLE_A_DISTANCE}/', '/{CABLE_B_STATUS}/', '/{CABLE_B_DISTANCE}/',
            '/{ROWS}/', '/{SWITCH_LAST_DATE}/', '/{SWITCH_LAST_PORT}/', '/{SWITCH_LAST_WORKER}/');

        $replacements = array($host, $btnChangeIfAdminStatus, $edgeCoreData->sysUpTime,
            $port, $edgeCoreData->ifLastChange, $ifOperStatus, $ifAdminStatus,
            $edgeCoreData->portSpeedDpxStatus, $edgeCoreData->portOutUtil,
            $edgeCoreData->portInUtil, $edgeCoreData->cableDiagResultTime,
            $edgeCoreData->cableDiagResultStatusPairA->status,
            $edgeCoreData->cableDiagResultDistancePairA,
            $edgeCoreData->cableDiagResultStatusPairB->status,
            $edgeCoreData->cableDiagResultDistancePairB, $rows, $switchLast->date,
            $switchLast->port, $switchLast->worker);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}GrayIPInfo.tpl"));
    }

    public static function getWhiteSwitchInfo($host, $ip, $device) {
        $patterns = array('/{HOST}/', '/{IP}/');

        $replacements = array($host, $ip);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}WhiteIPInfo.tpl"));
    }

    public static function getPonSwitchInfo($host, $portMac, $device) {
        $BDComData = BDCom::getData($host, $portMac);

        $patterns = array('/{HOST}/', '/{PORT_MAC}/');

        $replacements = array($host, $portMac);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}PONInfo.tpl"));
    }

    public static function getWirelessSwitchInfo($host, $ip, $device) {
        $patterns = array('/{HOST}/', '/{IP}/');

        $replacements = array($host, $ip);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}WirelessInfo.tpl"));
    }

    public static function getBitrixForm($contractData, $device) {
        $patterns = array('/{ADDRESS}/', '/{PHONE}/');

        $replacements = array($contractData->address, $contractData->phone);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}BitrixForm.tpl"));
    }

    private function getCountDays($tariff, $balance) {
        switch ($tariff) {
            case '2018 Активный (25М/300Р) - Архив 2018':
                $cost = 250;
                break;

            default:
                $cost = 300;
                break;
        }

        return floor($balance/($cost/intval(date("t"))));
    }

    private function getMacVendor($mac) {
        $url = 'https://2ip.ua/ru/services/information-service/mac-find';
        $post['a'] = 'act';
        $post['mac'] = $mac;
        $response = cURL::executeRequest($url, $post, false);
        $vendor = array();
        preg_match('/Имя компании:.*\n\t{5}<td>.*<\/td>/m', $response, $vendor);

        return substr(strrchr($vendor[0], ":"), 16);
    }
}