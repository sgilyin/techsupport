<?php

/*
 * Copyright (C) 2021 Sergey Ilyin <developer@ilyins.ru>
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
 * Class for SNR
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class SNR {
    public static function getData($host, $port) {
        $OIDs = array(
            'sysUpTime' => 'iso.3.6.1.2.1.1.3.0',
            'ifSpeed' => "iso.3.6.1.2.1.2.2.1.5.$port",
            'ifAdminStatus' => "iso.3.6.1.2.1.2.2.1.7.$port",
            'ifOperStatus' => "iso.3.6.1.2.1.2.2.1.8.$port",
            'ifLastChange' => "iso.3.6.1.2.1.2.2.1.9.$port",
            'portBandWidthUsage' => "iso.3.6.1.4.1.40418.7.100.3.2.1.23.$port",
            'vctLastStatus' => "iso.3.6.1.4.1.40418.7.100.3.2.1.19.$port",
        );
        $SNMPData = snmp2_get($host, SNMP_COMMUNITY_SNR, $OIDs);
        $sysUpTime = preg_replace('/^\D*(\d*)\).*/', "$1", $SNMPData[$OIDs['sysUpTime']]);
        $ifLastChange = preg_replace('/^\D*(\d*)\).*/', "$1", $SNMPData[$OIDs['ifLastChange']]);
        $data = new stdClass();
        $data->sysUpTime = ($SNMPData) ? Core::cleanSNMPValue($SNMPData[$OIDs['sysUpTime']]) : '-';
        $data->ifSpeed = ($SNMPData) ? intval(Core::cleanSNMPValue($SNMPData[$OIDs['ifSpeed']]))/1000000 : '-';
        $data->ifLastChange = ($SNMPData) ? Core::timeticksConvert(intval($ifLastChange)) : '-';
        $data->ifAdminStatus = ($SNMPData) ? intval(Core::cleanSNMPValue($SNMPData[$OIDs['ifAdminStatus']])) : '-';
        $data->ifOperStatus = ($SNMPData) ? intval(Core::cleanSNMPValue($SNMPData[$OIDs['ifOperStatus']])) : '-';
        $data->ifUsage = ($SNMPData) ? preg_replace('/[" ]/', '', Core::cleanSNMPValue($SNMPData[$OIDs['portBandWidthUsage']])) : '-';
        $vctLastStatus = Core::cleanSNMPValue($SNMPData[$OIDs['vctLastStatus']]);
        preg_match_all('/\(\d\,[[:blank:]]\d\)[[:blank:]]+\b.+\b[[:blank:]]+\d+/', $vctLastStatus, $cableTest);
        $data->cableDiag = ($SNMPData) ? self::cableDiagText($cableTest[0]) : '-';
        $data->devices = self::getPortDevices($host, $port);
        return $data;
    }

    private static function getPortDevices($host, $port) {
        $dot1dTpFdbPort = snmp2_real_walk($host, SNMP_COMMUNITY_SNR, 'iso.3.6.1.2.1.17.4.3.1.2');
        $dhcpSnoopingAckBindingPort = snmp2_real_walk($host, SNMP_COMMUNITY_SNR, 'iso.3.6.1.4.1.40418.7.100.13.3.20.1.3');
        $devices = array();
        $idDevices = array();
        $oids = array();
        foreach ($dot1dTpFdbPort as $key => $value) {
            if (Core::cleanSNMPValue($value) == $port){
                $oidPart = explode(".", $key);
                $id = implode('.', array_slice($oidPart, -6));
                if (!in_array($id, $idDevices)) {
                    $idDevices[] = $id;
                }
            }
        }
        foreach ($dhcpSnoopingAckBindingPort as $key => $value) {
            if (Core::cleanSNMPValue($value) == $port){
                $oidPart = explode(".", $key);
                $id = implode('.', array_slice($oidPart, -4));
                if (!in_array($id, $idDevices)) {
                    $idDevices[] = $id;
                }
            }
        }
        foreach ($idDevices as $id) {
            $oidPart = explode(".", $id);
            switch (count($oidPart)) {
                case 4:
                    $oids[] = "iso.3.6.1.4.1.40418.7.100.13.3.20.1.2.$id";
                    $oids[] = "iso.3.6.1.4.1.40418.7.100.13.3.20.1.4.$id";
                    break;
                default:
                    $oids[] = "iso.3.6.1.2.1.17.4.3.1.1.$id";
                    break;
            }
        }
        $macAndIp = snmp2_get($host, SNMP_COMMUNITY_SNR, $oids);
        foreach ($macAndIp as $key => $value) {
            $oidPart = explode(".", $key);
            $id = implode('.', array_slice($oidPart, -4));
            switch (count($oidPart)) {
                case 18:
                    switch ($oidPart[13]) {
                        case 2:
                            $mac = mb_strtoupper(preg_replace('/[ "-]/', '', Core::cleanSNMPValue($value)));
                            foreach ($devices as $key => $val) {
                               if ($val['mac'] === $mac) {
                                   unset($devices[$key]);
                                }
                            }
                            $devices[$id]['mac'] = $mac;
                            break;
                        case 4:
                            $devices[$id]['ip'] = implode('.', array_slice($oidPart, -4));
                            $devices[$id]['vlan'] = Core::cleanSNMPValue($value);
                            break;
                    }
                    break;
                default:
                    $devices[$id]['mac'] = preg_replace('/[ -]/', '', Core::cleanSNMPValue($value));
                    break;
            }
        }
        return $devices;
    }

    private static function cableDiagText($cableTest) {
        return implode('<br>', $cableTest);
    }
 
    public static function changeIfAdminStatus($host, $port, $status) {
        snmp2_set($host, SNMP_COMMUNITY_SNR, "iso.3.6.1.4.1.40418.7.100.3.2.1.12.$port", "i", $status);
        sleep(4);
    }

    public static function cableTest($host, $port) {
        snmp2_set($host, SNMP_COMMUNITY_SNR, "iso.3.6.1.4.1.40418.7.100.3.2.1.18", "i", $port);
        sleep(4);
    }
}
