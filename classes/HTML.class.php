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
    /**
     * Get search form for techsupport
     * @param string $cid
     * @param string $device
     * @return string
     */
    public static function getSearchForm($cid, $device) {
        $patterns = array('/{CID}/');

        $replacements = array($cid);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}SearchForm.tpl"));
    }

    /**
     * Get block of contract information
     * @param stdObject $contractData
     * @param string $device
     * @return string
     */
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

    /**
     * Get block for gray ip information
     * @param string $host
     * @param string $port
     * @param string $device
     * @return string
     */
    public static function getGraySwitchInfo($host, $port, $device) {
        $edgeCoreData = EdgeCore::getData($host, $port);
        $ifOperStatus = ($edgeCoreData->ifOperStatus == 1)? 'Up' : '<font color="red"><b>Down</b></font>';
        $ifAdminStatus = ($edgeCoreData->ifAdminStatus == 2)? '. <font color="red"><b>Shutdown!</b></font>' : '';
        $switchLast = BGB::getLastWorker($host)->fetch_object();
        $cableTestLink = "<a target=_blank href='/cabletest/?host=$host'>$host</a>";

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
            '/{ROWS}/', '/{SWITCH_LAST_DATE}/', '/{SWITCH_LAST_PORT}/',
            '/{SWITCH_LAST_WORKER}/', '/{CABLE_TEST_LINK}/');

        $replacements = array($host, $btnChangeIfAdminStatus, $edgeCoreData->sysUpTime,
            $port, $edgeCoreData->ifLastChange, $ifOperStatus, $ifAdminStatus,
            $edgeCoreData->portSpeedDpxStatus, $edgeCoreData->portOutUtil,
            $edgeCoreData->portInUtil, $edgeCoreData->cableDiagResultTime,
            $edgeCoreData->cableDiagResultStatusPairA->status,
            $edgeCoreData->cableDiagResultDistancePairA,
            $edgeCoreData->cableDiagResultStatusPairB->status,
            $edgeCoreData->cableDiagResultDistancePairB, $rows, $switchLast->date,
            $switchLast->port, $switchLast->worker, $cableTestLink);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}GrayIPInfo.tpl"));
    }

    /**
     * Get block for white ip information
     * @param string $host
     * @param string $ip
     * @param string $device
     * @return string
     */
    public static function getWhiteSwitchInfo($host, $ip, $device) {
        $patterns = array('/{HOST}/', '/{IP}/');

        $replacements = array($host, $ip);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}WhiteIPInfo.tpl"));
    }

    /**
     * Get block for PON information
     * @param string $host
     * @param string $portMac
     * @param string $device
     * @return string
     */
    public static function getPonSwitchInfo($host, $portMac, $device) {
        $BDComData = BDCom::getData($host, $portMac);
        $ifOperStatus = ($BDComData->ifOperStatus == 1)? 'Up' : '<font color="red"><b>Down</b></font>';
        $ifAdminStatus = ($BDComData->ifAdminStatus == 2)? '. <font color="red"><b>Shutdown!</b></font>' : 'Up';
        $service = static::parse($portMac);
        $mapAddress = BGB::getSwitchAddress($host);
        $grachLink = "https://zbx.fialka.tv/d/d3FaolEMk/epon-interface?orgId=1&from=now-24h&to=now&var-Group=PON&var-Host={$mapAddress} ({$host})&var-port= EPON0/{$service->port}:{$service->llid}";

        if (isset($BDComData->nmsBindingsEntry)) {
            for ($i = 0; $i < count($BDComData->nmsBindingsEntry); $i++) {
                $rows .= "<tr><td>" . $BDComData->nmsBindingsEntry[$i]['vlan'] . "</td>
                    <td>" . $BDComData->nmsBindingsEntry[$i]['ip'] . "</td>
                    <td>" . gmdate("H:i:s", $BDComData->nmsBindingsEntry[$i]['lease']) . "</td>
                    <td>" . $BDComData->nmsBindingsEntry[$i]['mac'] . "</td>
                    <td>" . static::getMacVendor($BDComData->nmsBindingsEntry[$i]['mac']) . "</td></tr>";
            }
        }

        $patterns = array('/{HOST}/', '/{PORT_MAC}/', '/{SYS_UP_TIME}/', '/{IF_ADMIN_STATUS}/',
            '/{IF_OPER_STATUS}/', '/{OLT_RX_POWER}/', '/{ONU_STATUS}/', '/{IF_LAST_CHANGE}/',
            '/{ONU_DEREG_REASON}/', '/{ONU_RX_POWER}/', '/{ONU_TX_POWER}/', '/{ONU_CTV_POWER}/',
            '/{ROWS}/', '/{GRAPH_LINK}/');

        $replacements = array($host, $portMac, $BDComData->sysUpTime, $ifAdminStatus,
            $ifOperStatus, $BDComData->oltModuleRxPower, $BDComData->onuStatus,
            $BDComData->ifLastChange, $BDComData->onuDeregReason, $BDComData->onuModuleRxPower,
            $BDComData->onuModuleTxPower, $BDComData->onuCtvRxPower, $rows, $grachLink);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}PONInfo.tpl"));
    }

    /**
     * Get block for wireless information
     * @param string $host
     * @param string $ip
     * @param string $device
     * @return string
     */
    public static function getWirelessSwitchInfo($host, $ip, $device) {
        $patterns = array('/{HOST}/', '/{IP}/');

        $replacements = array($host, $ip);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}WirelessInfo.tpl"));
    }

    /**
     * Get form for creating BX24 task
     * @param stdObject $contractData
     * @param string $device
     * @return string
     */
    public static function getBitrixForm($contractData, $device) {
        $patterns = array('/{ADDRESS}/', '/{PHONE}/');

        $replacements = array($contractData->address, $contractData->phone);

        return preg_replace($patterns, $replacements, file_get_contents(__DIR__ .
                "/../templates/{$device}BitrixForm.tpl"));
    }

    /**
     * Get count days to deactivate contract
     * @param string $tariff
     * @param string $balance
     * @return floor
     */
    private function getCountDays($tariff, $balance) {
        switch ($tariff) {
            case '2018 Активный (25М/300Р) - Архив 2018':
                $cost = 250;
                break;
            case '2018 GePON 100 (100М+ТВ/650Р)':
                $cost = 650;
                break;
            case '2018 GePON 100 (100М/550Р)':
                $cost = 550;
                break;

            default:
                $cost = 300;
                break;
        }

        return floor($balance/($cost/intval(date("t"))));
    }

    /**
     * Get vendor at the MAC address
     * @param string $mac
     * @return string
     */
    private function getMacVendor($mac) {
        $url = 'https://2ip.ua/ru/services/information-service/mac-find';
        $post['a'] = 'act';
        $post['mac'] = $mac;
        $response = cURL::executeRequest($url, $post, false);
        $vendor = array();
        preg_match('/Имя компании:.*\n\t{5}<td>.*<\/td>/m', $response, $vendor);

        return substr(strrchr($vendor[0], ":"), 16);
    }

    /**
     * Parse service title
     * @param string $serviceTitle
     * @return \stdClass
     */
    private function parse($serviceTitle) {
        if (preg_match('/\/([1-4])\:(\d*)\(([0-9,A-F,a-f]{12})\)/', $serviceTitle, $matches)) {
            $service = new stdClass;
            $service->port = $matches[1];
            $service->llid = $matches[2];
            $service->mac = $matches[3];

            return $service;
        }
    }
}