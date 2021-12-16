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
    public static function getData($host, $port) {
        $OIDs = array(
            'sysUpTime' => 'iso.3.6.1.2.1.1.3.0',
            'ifSpeed' => "iso.3.6.1.2.1.2.2.1.5.$port",
            'ifAdminStatus' => "iso.3.6.1.2.1.2.2.1.7.$port",
            'ifOperStatus' => "iso.3.6.1.2.1.2.2.1.8.$port",
            'ifLastChange' => "iso.3.6.1.2.1.2.2.1.9.$port",
            'portInUtil' => "iso.3.6.1.4.1.259.6.10.94.1.2.6.1.4.$port",
            'portOutUtil' => "iso.3.6.1.4.1.259.6.10.94.1.2.6.1.7.$port",
            'cabDiagTime' => "iso.3.6.1.4.1.259.6.10.94.1.2.3.2.1.11.$port",
            'cabDiagDistA' => "iso.3.6.1.4.1.259.6.10.94.1.2.3.2.1.6.$port",
            'cabDiagDistB' => "iso.3.6.1.4.1.259.6.10.94.1.2.3.2.1.7.$port",
            'cabDiagStatA' => "iso.3.6.1.4.1.259.6.10.94.1.2.3.2.1.2.$port",
            'cabDiagStatB' => "iso.3.6.1.4.1.259.6.10.94.1.2.3.2.1.3.$port",
        );
        $data = new stdClass();
        $SNMPData = snmp2_get($host, SNMP_COMMUNITY_EDGECORE, $OIDs);
        $sysUpTime = preg_replace('/(^\D*)(\d*)(\).*)/', "$2",$SNMPData[$OIDs['sysUpTime']]);
        $ifLastChange = preg_replace('/(^\D*)(\d*)(\).*)/', "$2",$SNMPData[$OIDs['ifLastChange']]);
        $data->sysUpTime = ($SNMPData) ? Core::timeticksConvert($sysUpTime) : '-';
        $data->ifSpeed = ($SNMPData) ? intval(Core::cleanSNMPValue($SNMPData[$OIDs['ifSpeed']]))/1000000 : '-';
        $data->ifLastChange = ($SNMPData) ? Core::timeticksConvert(intval($sysUpTime)- intval($ifLastChange)) : '-';
        $data->ifAdminStatus = ($SNMPData) ? intval(Core::cleanSNMPValue($SNMPData[$OIDs['ifAdminStatus']])) : '-';
        $data->ifOperStatus = ($SNMPData) ? intval(Core::cleanSNMPValue($SNMPData[$OIDs['ifOperStatus']])) : '-';
        $data->ifUsage = ($SNMPData) ? self::ifUsageText($SNMPData[$OIDs['portInUtil']], $SNMPData[$OIDs['portOutUtil']]) : '-';
        $data->cableDiag = ($SNMPData) ? self::cableDiagText($SNMPData[$OIDs['cabDiagTime']], $SNMPData[$OIDs['cabDiagDistA']],
            $SNMPData[$OIDs['cabDiagDistB']], $SNMPData[$OIDs['cabDiagStatA']], $SNMPData[$OIDs['cabDiagStatB']]) : '-';
        $data->devices = self::getPortDevices($host, $port);
        return $data;
    }

    private static function getPortDevices($host, $port) {
        $dot1dTpFdbPort = snmp2_real_walk($host, SNMP_COMMUNITY_EDGECORE,'iso.3.6.1.2.1.17.4.3.1.2');
        $dhcpSnoopBindingsPortIfIndex = snmp2_real_walk($host,SNMP_COMMUNITY_EDGECORE, 'iso.3.6.1.4.1.259.6.10.94.1.46.4.1.1.6');
        $devices = $idDevices = $vlans = $oids = array();
        foreach ($dot1dTpFdbPort as $key => $value) {
            if (Core::cleanSNMPValue($value) == $port){
                $oidPart = explode(".", $key);
                $id = implode('.', array_slice($oidPart, -6));
                if (!in_array($id, $idDevices)) {
                    $idDevices[] = $id;
                }
            }
        }
        foreach ($dhcpSnoopBindingsPortIfIndex as $key => $value) {
            if (Core::cleanSNMPValue($value) == $port){
                $oidPart = explode(".", $key);
                $id = implode('.', array_slice($oidPart, -6));
                if (!in_array($id, $idDevices)) {
                    $idDevices[] = $id;
                }
                $vlan = intval($oidPart[16]-1000);
                if (!in_array($vlan, $vlans)) {
                    $vlans[] = $vlan;
                }
            }
        }
        foreach ($idDevices as $id) {
            $oids[] = "iso.3.6.1.2.1.17.4.3.1.1.$id";
            foreach ($vlans as $vlan) {
                $vlan = $vlan + 1000;
                $oids[] = "iso.3.6.1.4.1.259.6.10.94.1.46.4.1.1.5.$vlan.$id";
                $oids[] = "iso.3.6.1.4.1.259.6.10.94.1.46.4.1.1.7.$vlan.$id";
            }
        }
        $macAndIp = snmp2_get($host, SNMP_COMMUNITY_EDGECORE, $oids);
        foreach ($macAndIp as $key => $value) {
            $oidPart = explode(".", $key);
            $id = implode('.', array_slice($oidPart, -6));
            switch (count($oidPart)) {
                case 23:
                    switch ($oidPart[15]) {
                        case 5:
                            $devices[$id]['ip'] = Core::cleanSNMPValue($value);
                            $devices[$id]['vlan'] = $oidPart[16]-1000;
                            break;
                        case 7:
                            $devices[$id]['lease'] = Core::cleanSNMPValue($value);
                            break;
                    }
                    break;
                default:
                    $devices[$id]['mac'] = Core::cleanSNMPValue($value);
                    break;
            }
        }
        return $devices;
    }

    public static function cableTest($host, $port) {
        snmp2_set($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.2.3.1.0", "i", $port);
        sleep(4);
    }

    public static function changeIfAdminStatus($host, $port, $status) {
        snmp2_set($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.2.1.2.2.1.7.$port", "i", $status);
        sleep(4);
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

    private function ifUsageText($portInUtil, $portOutUtil) {
        $in = floatval(Core::cleanSNMPValue($portInUtil))/100;
        $out = floatval(Core::cleanSNMPValue($portOutUtil))/100;
        return "DL: $out Mbps<br>UL: $in Mbps";
    }

    private function cableDiagText($time, $distA, $distB, $statA, $statB) {
        $t = Core::cleanSNMPValue($time);
        $dA = Core::cleanSNMPValue($distA);
        $dB = Core::cleanSNMPValue($distB);
        $sA = self::cableDiagResultStatus(Core::cleanSNMPValue($statA))->status;
        $sB = self::cableDiagResultStatus(Core::cleanSNMPValue($statB))->status;
        return "$t<br>$sA ($dA) | $sB ($dB)";
    }
}
