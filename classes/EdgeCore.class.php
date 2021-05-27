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
 * Class for EdgeCore
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class EdgeCore {
    /**
     * Get data from switch via SNMP
     * @param string $host
     * @param string $port
     * @return \stdClass
     */
    public static function getData($host, $port) {
        $OIDs = array(
            'sysUpTime' => 'iso.3.6.1.2.1.1.3.0',
            'ifAdminStatus' => "iso.3.6.1.2.1.2.2.1.7.$port",
            'ifOperStatus' => "iso.3.6.1.2.1.2.2.1.8.$port",
            'ifLastChange' => "iso.3.6.1.2.1.2.2.1.9.$port",
            'portInUtil' => "iso.3.6.1.4.1.259.6.10.94.1.2.6.1.4.$port",
            'portOutUtil' => "iso.3.6.1.4.1.259.6.10.94.1.2.6.1.7.$port",
            'cableDiagResultTime' => "iso.3.6.1.4.1.259.6.10.94.1.2.3.2.1.11.$port",
            'cableDiagResultDistancePairA' => "iso.3.6.1.4.1.259.6.10.94.1.2.3.2.1.6.$port",
            'cableDiagResultDistancePairB' => "iso.3.6.1.4.1.259.6.10.94.1.2.3.2.1.7.$port",
            'cableDiagResultStatusPairA' => "iso.3.6.1.4.1.259.6.10.94.1.2.3.2.1.2.$port",
            'cableDiagResultStatusPairB' => "iso.3.6.1.4.1.259.6.10.94.1.2.3.2.1.3.$port",
            'portSpeedDpxStatus' => "iso.3.6.1.4.1.259.6.10.94.1.2.1.1.8.$port"
        );
        $SNMPData = snmp2_get($host, SNMP_COMMUNITY_EDGECORE, $OIDs);
        $data = new stdClass();
        $sysUpTime = preg_replace('/(^\D*)(\d*)(\).*)/', "$2", $SNMPData[$OIDs['sysUpTime']]);
        $ifLastChange = preg_replace('/(^\D*)(\d*)(\).*)/', "$2", $SNMPData[$OIDs['ifLastChange']]);
        $data->sysUpTime = ($SNMPData) ? static::timeticksConvert($sysUpTime) : '-';
        $data->ifLastChange = ($SNMPData) ? static::timeticksConvert(intval($sysUpTime)- intval($ifLastChange)) : '-';
        $data->ifAdminStatus = ($SNMPData) ? intval(static::cleanValue($SNMPData[$OIDs['ifAdminStatus']])) : '-';
        $data->ifOperStatus = ($SNMPData) ? intval(static::cleanValue($SNMPData[$OIDs['ifOperStatus']])) : '-';
        $data->portInUtil = ($SNMPData) ? floatval(static::cleanValue($SNMPData[$OIDs['portInUtil']]))/100 : '-';
        $data->portOutUtil = ($SNMPData) ? floatval(static::cleanValue($SNMPData[$OIDs['portOutUtil']]))/100 : '-';
        $data->cableDiagResultTime = ($SNMPData) ? static::cleanValue($SNMPData[$OIDs['cableDiagResultTime']]) : '-';
        $data->cableDiagResultDistancePairA = ($SNMPData) ? intval(static::cleanValue($SNMPData[$OIDs['cableDiagResultDistancePairA']])) : '-';
        $data->cableDiagResultDistancePairB = ($SNMPData) ? intval(static::cleanValue($SNMPData[$OIDs['cableDiagResultDistancePairB']])) : '-';
        $data->cableDiagResultStatusPairA = ($SNMPData) ? static::cableDiagResultStatus(intval(static::cleanValue($SNMPData[$OIDs['cableDiagResultStatusPairA']]))) : '-';
        $data->cableDiagResultStatusPairB = ($SNMPData) ? static::cableDiagResultStatus(intval(static::cleanValue($SNMPData[$OIDs['cableDiagResultStatusPairB']]))) : '-';
        $data->portSpeedDpxStatus = ($SNMPData) ? static::portSpeedDpxStatus(intval(static::cleanValue($SNMPData[$OIDs['portSpeedDpxStatus']]))) : '-';
        $dhcpSnoopBindingsTable = ($SNMPData) ? snmp2_real_walk($host, SNMP_COMMUNITY_EDGECORE, '.1.3.6.1.4.1.259.6.10.94.1.46.4.1') : array();
        $macAddressTable = ($SNMPData) ? snmp2_real_walk($host, SNMP_COMMUNITY_EDGECORE, '.1.3.6.1.2.1.17.4.3.1.1') : array();
        $macPortTable = ($SNMPData) ? snmp2_real_walk($host, SNMP_COMMUNITY_EDGECORE, '.1.3.6.1.2.1.17.4.3.1.2') : array();
        $dhcpSnoopBindingsTableTotal = array();
        $dhcpSnoopBindingsTablePort = array();

        foreach ($dhcpSnoopBindingsTable as $key => $value) {
            $oidPart = explode(".", $key);
            $id = $oidPart[17] . '.' . $oidPart[18] . '.' . $oidPart[19] . '.' . $oidPart[20] . '.' . $oidPart[21] . '.' . $oidPart[22];
            $vlan = intval($oidPart[16]-1000);
            $uids = array(5, 6, 7);
            if (in_array($oidPart[15], $uids)) {
                $k = static::oidName($oidPart[15]);
                $dhcpSnoopBindingsTableTotal[$id][$k] = static::cleanValue($value);
            }
            $dhcpSnoopBindingsTableTotal[$id]['vlan'] = $vlan;
        }

        foreach ($macAddressTable as $key => $value) {
            $oidPart = explode(".", $key);
            $id = $oidPart[11] . '.' . $oidPart[12] . '.' . $oidPart[13] . '.' . $oidPart[14] . '.' . $oidPart[15] . '.' . $oidPart[16];
            $dhcpSnoopBindingsTableTotal[$id]['mac'] = substr(static::cleanValue($value), 0, -1);
        }

        foreach ($macPortTable as $key => $value) {
            $oidPart = explode(".", $key);
            $id = $oidPart[11] . '.' . $oidPart[12] . '.' . $oidPart[13] . '.' . $oidPart[14] . '.' . $oidPart[15] . '.' . $oidPart[16];
            $dhcpSnoopBindingsTableTotal[$id]['port'] = static::cleanValue($value);
            if ($dhcpSnoopBindingsTableTotal[$id]['port'] == $port) {
                    $dhcpSnoopBindingsTablePort[] = $id;
            }
        }

        for ($i = 0; $i < count($dhcpSnoopBindingsTablePort); $i++) {
            $data->dhcpSnoopBinPort[] = $dhcpSnoopBindingsTableTotal[$dhcpSnoopBindingsTablePort[$i]];
        }

        return $data;
    }

    /**
     * Make cable test on switch for port
     * @param string $host
     * @param string $port
     */
    public static function cableTest($host, $port) {
        snmp2_set($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.2.3.1.0", "i", $port);
        sleep(4);
    }

    /**
     * Change ifAdminStatus on switch for port
     * @param string $host
     * @param string $port
     * @param string $status
     */
    public static function changeIfAdminStatus($host, $port, $status) {
        snmp2_set($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.2.1.2.2.1.7.$port", "i", $status);
        sleep(4);
    }

    /**
     * Remove service information from value
     * @param string $value
     * @return string
     */
    private function cleanValue ($value) {
        $patterns = array('/IpAddress: /', '/INTEGER: /', '/Gauge32: /', '/Hex-STRING: /', '/STRING: /');

        return str_replace(' ', '-', preg_replace($patterns, '', $value));
#        return preg_replace($patterns, '', $value);
    }

    /**
     * Get text OID Name
     * @param integer $oidId
     * @return string
     */
    private function oidName ($oidId) {
        switch ($oidId) {
            case 3:
                $oidName = 'AddrType';
                break;
            case 4:
                $oidName = 'EntryType';
                break;
            case 5:
                $oidName = 'IpAddress';
                break;
            case 6:
                $oidName = 'PortIfIndex';
                break;
            case 7:
                $oidName = 'LeaseTime';
                break;
        }

        return $oidName;
    }

    /**
     * Convert timeticks to string
     * @param string $timeticks
     * @return string
     */
    private function timeticksConvert($timeticks){
        $lntSecs = intval($timeticks / 100);
	$intDays = intval($lntSecs / 86400);
	$intHours = intval(($lntSecs - ($intDays * 86400)) / 3600);
	$intMinutes = intval(($lntSecs - ($intDays * 86400) - ($intHours * 3600)) / 60);
	$intSeconds = intval(($lntSecs - ($intDays * 86400) - ($intHours * 3600) - ($intMinutes * 60)));
        $strHours = str_pad($intHours, 2, '0', STR_PAD_LEFT);
        $strMinutes = str_pad($intMinutes, 2, '0', STR_PAD_LEFT);
        $strSeconds = str_pad($intSeconds, 2, '0', STR_PAD_LEFT);
	
	return "$intDays days, $strHours:$strMinutes:$strSeconds"; 
    }

    /**
     * Get text for port speed and duplex
     * @param integer $param
     * @return string
     */
    private function portSpeedDpxStatus($param) {
        switch ($param) {
            case 1:
                $status = 'error';
                break;
            case 2:
                $status = 'HDX-10';
                break;
            case 3:
                $status = 'FDX-10';
                break;
            case 4:
                $status = 'HDX-100';
                break;
            case 5:
                $status = 'FDX-100';
                break;
            case 6:
                $status = 'HDX-1G';
                break;
            case 7:
                $status = 'FDX-1G';
                break;
            case 8:
                $status = 'HDX-10G';
                break;
            case 9:
                $status = 'FDX-10G';
                break;

            default:
                break;
        }
        return $status;
    }

    /**
     * Get text for cable diagnostic
     * @param integer $param
     * @return \stdClass
     */
    private function cableDiagResultStatus($param) {
        $result = new stdClass();
        switch ($param) {
            case 1:
                $result->status = 'notTestedYet';
                $result->hint = 'Значение "notTestedYet" означает, что пара еще не проверена.';
                break;
            case 2:
                $result->status = 'OK';
                $result->hint = 'Значение "OK" означает, что пара работает хорошо.';
                break;
            case 3:
                $result->status = 'open';
                $result->hint = 'Значение "open" означает отсутствие непрерывности между контактами на каждом конце пары.';
                break;
            case 4:
                $result->status = 'short';
                $result->hint = 'Значение "short" означает, что провода замкнуты вместе на паре.';
                break;
            case 5:
                $result->status = 'openShort';
                $result->hint = 'Значение "openShort" означает, что пара открыта и замкнута.';
                break;
            case 6:
                $result->status = 'crosstalk';
                $result->hint = 'Значение "crossstalk" означает, что пара неправильно подключена на одном конце.';
                break;
            case 7:
                $result->status = 'unknown';
                $result->hint = 'Значение "unknown" означает, что замер произведен неудачно или кабель слишком короткий.';
                break;
            case 8:
                $result->status = 'impedance Mismatch';
                $result->hint = 'Значение "impedanceMismatch" означает, что кабели различного качества связаны друг с другом.';
                break;
            case 9:
                $result->status = 'fail';
                $result->hint = 'Значение "fail" означает, что тест не пройден.';
                break;
            case 10:
                $result->status = 'notSupport';
                $result->hint = 'Значение "notSupport" означает, что диагностика кабеля не поддерживается.';
                break;

            default:
                break;
        }
        return $result;
    }
}