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
        $data->ifLastChange = ($SNMPData) ? Core::timeticksConvert(intval($sysUpTime)- intval($ifLastChange)) : '-';
        $data->ifAdminStatus = ($SNMPData) ? intval(Core::cleanSNMPValue($SNMPData[$OIDs['ifAdminStatus']])) : '-';
        $data->ifOperStatus = ($SNMPData) ? intval(Core::cleanSNMPValue($SNMPData[$OIDs['ifOperStatus']])) : '-';
        $data->ifUsage = ($SNMPData) ? Core::cleanSNMPValue($SNMPData[$OIDs['portBandWidthUsage']]) : '-';
        $vctLastStatus = Core::cleanSNMPValue($SNMPData[$OIDs['vctLastStatus']]);
        preg_match_all('/\(\d\,[[:blank:]]\d\)[[:blank:]]+\b.+\b[[:blank:]]+\d+/', $vctLastStatus, $cableTest);
        $data->cableDiag = ($SNMPData) ? self::cableDiagText($cableTest[0]) : '-';
        return $data;
    }

    private static function getPortDevices($host, $port) {
        
    }

    private static function cableDiagText($cableTest) {
        return implode('<br>', $cableTest);
    }
}
