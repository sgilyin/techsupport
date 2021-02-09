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
 * Description of BDCom
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class BDCom {
    public static function getData($host, $port) {
        var_dump($host);
        var_dump($port);
        $data = new stdClass();
        $sysUpTime = snmp2_get($host, SNMP_COMMUNITY_BDCOM, '.1.3.6.1.2.1.1.3.0');
        var_dump($sysUpTime);
        var_dump($data);
        #$data->sysUpTime = static::timeticksConvert($sysUpTime);
        #$ifLastChange = preg_replace('/(^\D*)(\d*)(\).*)/', "$2", snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.2.1.2.2.1.9.$port"));
        #$data->ifLastChange = static::timeticksConvert(intval($sysUpTime)- intval($ifLastChange));
        #$data->swPortNumber = intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.1.3.1.7.1")));
        #$data->dhcpSnoopBindingsIpAddress = preg_replace('/IpAddress: /m', '', snmp2_walk($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.46.4.1.1.5"));
        #$data->dhcpSnoopBindingsLeaseTime = preg_replace('/Gauge32: /m', '', snmp2_walk($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.46.4.1.1.7"));
        #$data->macs = preg_replace('/Hex-STRING: /m', '', snmp2_walk($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.2.1.17.4.3.1.1"));
        #$data->macPorts = preg_replace('/INTEGER: /m', '', snmp2_walk($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.2.1.17.4.3.1.2"));
        #$data->ifAdminStatus = intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.2.1.2.2.1.7.$port")));
        #$data->ifOperStatus = intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.2.1.2.2.1.8.$port")));
        #$data->portInUtil = floatval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.2.6.1.4.$port")))/100;
        #$data->portOutUtil = floatval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.2.6.1.7.$port")))/100;
        #$data->cableDiagResultTime = preg_replace('/STRING: /m', '', snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.2.3.2.1.11.$port"));
        #$data->cableDiagResultDistancePairA = intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.2.3.2.1.6.$port")));
        #$data->cableDiagResultDistancePairB = intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.2.3.2.1.7.$port")));
        #$data->cableDiagResultStatusPairA = static::cableDiagResultStatus(intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.2.3.2.1.2.$port"))));
        #$data->cableDiagResultStatusPairB = static::cableDiagResultStatus(intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.2.3.2.1.3.$port"))));
        #$data->portSpeedDpxStatus = static::portSpeedDpxStatus(intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY_EDGECORE, ".1.3.6.1.4.1.259.6.10.94.1.2.1.1.8.$port"))));
        #$dhcpSnoopBindingsTable = snmp2_real_walk($host, SNMP_COMMUNITY_EDGECORE, '.1.3.6.1.4.1.259.6.10.94.1.46.4.1');
        #$macAddressTable = snmp2_real_walk($host, SNMP_COMMUNITY_EDGECORE, '.1.3.6.1.2.1.17.4.3.1.1');
        #$macPortTable = snmp2_real_walk($host, SNMP_COMMUNITY_EDGECORE, '.1.3.6.1.2.1.17.4.3.1.2');

        #$dhcpSnoopBindingsTableTotal = array();
        #$dhcpSnoopBindingsTablePort = array();
/*
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
*/
        return $data;
    }
}
