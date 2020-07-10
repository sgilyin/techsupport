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
        $data = new stdClass();
        $data->sysUpTime = preg_replace('/Timeticks: \(\d*\) /m', '', snmp2_get($host, SNMP_COMMUNITY, '.1.3.6.1.2.1.1.3.0'));
        $data->ifLastChange = date("H:i:s", $data->sysUpTime.' - '.preg_replace('/Timeticks: \(\d*\) /m', '', snmp2_get($host, SNMP_COMMUNITY, ".1.3.6.1.2.1.2.2.1.9.$port")));
//        $data->swPortNumber = intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY, ".1.3.6.1.4.1.259.6.10.94.1.1.3.1.7.1")));
        $data->dhcpSnoopBindingsIpAddress = preg_replace('/IpAddress: /m', '', snmp2_walk($host, SNMP_COMMUNITY, ".1.3.6.1.4.1.259.6.10.94.1.46.4.1.1.5"));
        $data->dhcpSnoopBindingsLeaseTime = preg_replace('/Gauge32: /m', '', snmp2_walk($host, SNMP_COMMUNITY, ".1.3.6.1.4.1.259.6.10.94.1.46.4.1.1.7"));
        $data->macs = preg_replace('/Hex-STRING: /m', '', snmp2_walk($host, SNMP_COMMUNITY, ".1.3.6.1.2.1.17.4.3.1.1"));
        $data->macPorts = preg_replace('/INTEGER: /m', '', snmp2_walk($host, SNMP_COMMUNITY, ".1.3.6.1.2.1.17.4.3.1.2"));
        $data->ifOperStatus = intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY, ".1.3.6.1.2.1.2.2.1.8.$port")));
        $data->portInUtil = floatval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY, ".1.3.6.1.4.1.259.6.10.94.1.2.6.1.4.$port")))/100;
        $data->portOutUtil = floatval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY, ".1.3.6.1.4.1.259.6.10.94.1.2.6.1.7.$port")))/100;
        $data->cableDiagResultTime = preg_replace('/STRING: /m', '', snmp2_get($host, SNMP_COMMUNITY, ".1.3.6.1.4.1.259.6.10.94.1.2.3.2.1.11.$port"));
        $data->cableDiagResultDistancePairA = intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY, ".1.3.6.1.4.1.259.6.10.94.1.2.3.2.1.6.$port")));
        $data->cableDiagResultDistancePairB = intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY, ".1.3.6.1.4.1.259.6.10.94.1.2.3.2.1.7.$port")));
        $data->cableDiagResultStatusPairA = static::cableDiagResultStatus(intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY, ".1.3.6.1.4.1.259.6.10.94.1.2.3.2.1.2.$port"))));
        $data->cableDiagResultStatusPairB = static::cableDiagResultStatus(intval(preg_replace('/INTEGER: /m', '', snmp2_get($host, SNMP_COMMUNITY, ".1.3.6.1.4.1.259.6.10.94.1.2.3.2.1.3.$port"))));


        return $data;
    }

    public static function cableTest($host, $port) {
        snmp2_set($host, SNMP_COMMUNITY, ".1.3.6.1.4.1.259.6.10.94.1.2.3.1.0", "i", $port);
        sleep(3);
        
    }

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
                $result->status = 'impedanceMismatch';
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

    public static function getLog($switch) {
        $mysqli = new mysqli(RSYSLOG_HOST, RSYSLOG_USER, RSYSLOG_PASS, RSYSLOG_DB);
        if (mysqli_connect_errno()) {
            printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
            exit;
        }
        $mysqli->query("set character_set_client='utf8'");
        $mysqli->query("set character_set_results='utf8'");
        $mysqli->query("set collation_connection='utf8_general_ci'");
        $result = $mysqli->query("SELECT devicereportedtime, message FROM SystemEvents WHERE fromhost = '$switch' ORDER BY id DESC LIMIT 10");
        for($i = 0; $i < $result->num_rows; $i++){
            $rSysLog = $result->fetch_object();
            $tableRows = $tableRows."| $rSysLog->devicereportedtime | $rSysLog->message<br>";
        }

        return $tableRows;
    }
}
