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
    /**
     * Get data from switch via SNMP
     * @param string $serviceHost
     * @param string $serviceTitle
     * @return \stdClass
     */
    public static function getData($serviceHost, $serviceTitle) {
        $service = static::parse($serviceTitle);
        $service->host = $serviceHost;
        $onuMacAddressIndex = static::getOnuStatId($service);
        $data = new stdClass();
        $sysUpTime = preg_replace('/(^\D*)(\d*)(\).*)/', "$2", snmp2_get($serviceHost, SNMP_COMMUNITY_BDCOM, '.1.3.6.1.2.1.1.3.0'));
        $data->sysUpTime = static::timeticksConvert($sysUpTime);
        $service->onuId = static::getOnuId($service);
        $data->ifIndex = $service->onuId;
        $ifLastChange = preg_replace('/(^\D*)(\d*)(\).*)/', "$2", snmp2_get($service->host, SNMP_COMMUNITY_BDCOM, ".1.3.6.1.2.1.2.2.1.9.{$service->onuId}"));
        $data->ifAdminStatus = intval(static::cleanValue(snmp2_get($service->host, SNMP_COMMUNITY_BDCOM, ".1.3.6.1.2.1.2.2.1.7.{$service->onuId}")));
        $data->ifOperStatus = intval(static::cleanValue(snmp2_get($service->host, SNMP_COMMUNITY_BDCOM, ".1.3.6.1.2.1.2.2.1.8.{$service->onuId}")));
        $data->ifLastChange = static::timeticksConvert(intval($sysUpTime)- intval($ifLastChange));
        $data->onuStatus = static::getOnuStatus(intval(static::cleanValue(snmp2_get($service->host, SNMP_COMMUNITY_BDCOM, ".1.3.6.1.4.1.3320.101.11.1.1.6.{$onuMacAddressIndex}"))));
        $data->onuDeregReason = static::getOnuDeregReason(intval(static::cleanValue(snmp2_get($service->host, SNMP_COMMUNITY_BDCOM, ".1.3.6.1.4.1.3320.101.11.1.1.11.{$onuMacAddressIndex}"))));
        $data->onuModuleRxPower = intval(static::cleanValue(snmp2_get($service->host, SNMP_COMMUNITY_BDCOM, ".1.3.6.1.4.1.3320.101.10.5.1.5.{$service->onuId}"))) * 0.1;
        $data->oltModuleRxPower = intval(static::cleanValue(snmp2_get($service->host, SNMP_COMMUNITY_BDCOM, ".1.3.6.1.4.1.3320.101.108.1.3.{$service->onuId}"))) * 0.1;
        $data->onuModuleTxPower = intval(static::cleanValue(snmp2_get($service->host, SNMP_COMMUNITY_BDCOM, ".1.3.6.1.4.1.3320.101.10.5.1.6.{$service->onuId}"))) * 0.1;
        $data->onuCtvRxPower = intval(static::cleanValue(snmp2_get($service->host, SNMP_COMMUNITY_BDCOM, ".1.3.6.1.4.1.3320.101.10.31.1.2.{$service->onuId}"))) * 0.1;
        $data->nmsBindingsEntry = static::getDhcpSnooping($service->host, $service->port, intval($service->llid));

        return $data;
    }

    /**
     * Get DHCP Snooping Table from switch via SNMP
     * @param string $host
     * @param integer $port
     * @param integer $llid
     * @return array
     */
    private function getDhcpSnooping($host, $port, $llid) {
        $SNMP = new SNMP(1, $host, SNMP_COMMUNITY_BDCOM);
        $SNMP->oid_increasing_check = false;
        $dataSNMP = $SNMP->walk(".1.3.6.1.4.1.3320.2.233.2.1");
        $SNMP->close();
        foreach ($dataSNMP as $key => $value) {
            if (intval(substr($key, -2)) == $port + 14) {
                $id = substr($key, 31);
                $oid = substr($key, 29, 1);
                switch ($oid) {
                        case '1':
                            $item = 'ip';
                            $ip4Octet = intval(preg_replace("/(\d{1,3})\.(\d{1,3}).(\d{1,3}).(\d{1,3})/", '$4', static::cleanValue($value)));
                            if ($ip4Octet != $llid + 10 && $ip4Octet != $llid + 100){
                                $unset[] = $id;
                            }
                            $arr[$id][$item] = static::cleanValue($value);
                            break;
                        case '2':
                            $item = 'mac';
                            $arr[$id][$item] = str_replace(' ', '', static::cleanValue($value));
                            break;
                        case '3':
                            $item = 'vlan';
                            $arr[$id][$item] = intval(static::cleanValue($value));
                            break;
                        case '5':
                            $item = 'lease';
                            $arr[$id][$item] = intval(static::cleanValue($value));
                            break;

                        default:
                            break;
                }
            }
        }

        foreach ($unset as $value) {
            unset($arr[$value]);
        }

        foreach ($arr as $value) {
            $result[] = $value;
        }

        return $result;
    }

    /**
     * Remove service information from value
     * @param string $value
     * @return string
     */
    private function cleanValue ($value) {
        $patterns = array('/IpAddress: /', '/INTEGER: /', '/Gauge32: /', '/Hex-STRING: /', '/STRING: /');

        return preg_replace($patterns, '', $value);
    }

    /**
     * Get ID ONU on switch
     * @param stdClass $service
     * @return integer
     */
    private function getOnuId($service) {
        $ifTable = snmp2_real_walk($service->host, SNMP_COMMUNITY_BDCOM, '.1.3.6.1.2.1.2.2.1.2');
        $id = array_search("STRING: \"EPON0/{$service->port}:{$service->llid}\"", $ifTable);

        return intval(preg_replace('/iso.3.6.1.2.1.2.2.1.2./', '', $id));
    }

    /**
     * Get ID ONU on switch for MAC address table
     * @param stdClass $service
     * @return string
     */
    private function getOnuStatId($service) {
        $onuMacAddressIndex = snmp2_real_walk($service->host, SNMP_COMMUNITY_BDCOM, '.1.3.6.1.4.1.3320.101.11.1.1.3');
        $macSpaced = preg_replace('/([0-9,A-F,a-f]{2})([0-9,A-F,a-f]{2})'
                . '([0-9,A-F,a-f]{2})([0-9,A-F,a-f]{2})([0-9,A-F,a-f]{2})'
                . '([0-9,A-F,a-f]{2})/', "$1 $2 $3 $4 $5 $6 ", $service->mac);
        $id = preg_replace('/iso.3.6.1.4.1.3320.101.11.1.1.3./', '', array_search("Hex-STRING: $macSpaced", $onuMacAddressIndex));

        return $id;
    }

    /**
     * Get text status of ONU
     * @param integer $status
     * @return string
     */
    private function getOnuStatus($status) {
        switch ($status) {
            case 0:
                $onuStatus = 'authenticated';
                break;
            case 1:
                $onuStatus = 'registered';
                break;
            case 2:
                $onuStatus = 'deregistered';
                break;
            case 3:
                $onuStatus = 'discovered';
                break;
            case 4:
                $onuStatus = 'lost';
                break;
            case 5:
                $onuStatus = 'auto_configured';
                break;

            default:
                $onuStatus = 'unknow';
                break;
        }

        return $onuStatus;
    }

    /**
     * Get text dereg reason of ONU
     * @param integer $reason
     * @return string
     */
    private function getOnuDeregReason($reason) {
        switch ($reason) {
            case 2:
                $onuDeregReason = 'normal';
                break;
            case 3:
                $onuDeregReason = 'mpcp-down';
                break;
            case 4:
                $onuDeregReason = 'oam-down';
                break;
            case 5:
                $onuDeregReason = 'firmware-download';
                break;
            case 6:
                $onuDeregReason = 'illegal-mac';
                break;
            case 7:
                $onuDeregReason = 'llid-admin-down';
                break;
            case 8:
                $onuDeregReason = 'wire-down';
                break;
            case 9:
                $onuDeregReason = 'power-off';
                break;

            default:
                $onuDeregReason = 'unknow';
                break;
        }

        return $onuDeregReason;
    }

    /**
     * Get parts of the service
     * @param string $serviceTitle
     * @return \stdClass
     */
    private function parse($serviceTitle) {
        if (preg_match('/\/([1-4])\:(\d*)\)?\(+([0-9,A-F,a-f]{12})\)/', $serviceTitle, $matches)) {
            $service = new stdClass;
            $service->port = $matches[1];
            $service->llid = $matches[2];
            $service->mac = $matches[3];

            return $service;
        }
    }

    /**
     * Convert timeticks to text
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

    public static function getLastDereg($host, $reason) {
        $SNMPData = snmp2_real_walk($host, SNMP_COMMUNITY_BDCOM, "iso.3.6.1.4.1.3320.101.11.1.1.6", 3000000);
        $i = 0;
        $arr = array();
        foreach ($SNMPData as $key => $value) {
            if (Core::cleanSNMPValue($value) != 5){
                $oidPart = explode(".", $key);
                $id = implode('.', array_slice($oidPart, -7));
                $oids = array(
                    'channel' => "iso.3.6.1.4.1.3320.101.11.1.1.2.$id",
                    'deregReason' => "iso.3.6.1.4.1.3320.101.11.1.1.11.$id",
                );
                $snmpGet = snmp2_get($host, SNMP_COMMUNITY_BDCOM, $oids);
                $deregReason = intval(Core::cleanSNMPValue($snmpGet[$oids['deregReason']]));
                if ($deregReason == 8) {
                    $arr[$i]['id'] = $id;
                    $arr[$i]['port'] = intval($oidPart[12])-14;
                    $arr[$i]['channel'] = intval(Core::cleanSNMPValue($snmpGet[$oids['channel']]));
                    $i++;
                }
            }
        }
        return $arr;
    }
}