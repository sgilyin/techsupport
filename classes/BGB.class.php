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
 * Class for BGB
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class BGB {
    /**
     * Execute SQL request to BGB
     * @param string $query
     * @return mixed
     */
    static function sql($query) {
        $mysqli = new mysqli(BGB_HOST, BGB_USER, BGB_PASS, BGB_DB);
        if (mysqli_connect_errno()) {
            printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
            exit;
        }
        $mysqli->query("set character_set_client='utf8'");
        $mysqli->query("set character_set_results='utf8'");
        $mysqli->query("set collation_connection='utf8_general_ci'");

        return $mysqli->query($query);
    }

    /**
     * Get last worker on switch
     * @param string $host
     * @return mixed
     */
    public static function getLastWorker($host) {
        $query = "
SELECT  t_csl.date date, t_is15.interfaceId port, t_is15.comment worker
FROM inet_serv_15 t_is15
JOIN inet_device_tree_15 t_idt15 ON t_idt15.id=t_is15.deviceId
JOIN inv_device_15 t_id15 ON t_id15.id=t_idt15.invDeviceId
LEFT JOIN contract_status_log t_csl ON t_is15.contractId=t_csl.cid
WHERE t_is15.dateFrom=t_csl.date1 AND t_csl.comment='Автоподключение' AND t_id15.host='$host'
ORDER BY t_is15.dateFrom DESC
LIMIT 1
            ";

        return static::sql($query);
    }

    /**
     * Get data from BGB about contract or services
     * @param string $cid
     * @param string $requestType
     * @return mixed
     */
    public static function getData($cid, $requestType) {
        switch ($requestType) {
            case 'contract':
                $query = "
SELECT tbl_cpt1_abonent.val abonent, CONCAT(tbl_street.title, ' д. ', tbl_house.house, CONCAT_WS( ' кв. ',tbl_house.frac, IF(tbl_flat.flat='',NULL,tbl_flat.flat))) address, tbl_cpt1_phone.val phone, tbl_cpt1_comment.val comment, IF(tbl_contract.status=0,'Активен','Не активен') status, tbl_cb.summa1+tbl_cb.summa2-tbl_cb.summa3-tbl_cb.summa4 balance, GROUP_CONCAT(tbl_tariff.tariff) tariff
FROM contract tbl_contract
LEFT JOIN contract_parameter_type_1 tbl_cpt1_abonent ON (tbl_contract.id=tbl_cpt1_abonent.cid AND (tbl_cpt1_abonent.pid=1 OR tbl_cpt1_abonent.pid=6))
LEFT JOIN contract_parameter_type_1 tbl_cpt1_comment ON (tbl_contract.id=tbl_cpt1_comment.cid AND tbl_cpt1_comment.pid=32)
LEFT JOIN contract_parameter_type_2 tbl_flat ON (tbl_contract.id=tbl_flat.cid)
LEFT JOIN address_house tbl_house ON (tbl_flat.hid=tbl_house.id)
LEFT JOIN address_street tbl_street ON (tbl_house.streetid=tbl_street.id)
LEFT JOIN contract_parameter_type_1 tbl_cpt1_phone ON (tbl_contract.id=tbl_cpt1_phone.cid AND tbl_cpt1_phone.pid=2)
LEFT JOIN contract_balance tbl_cb ON (tbl_contract.id=tbl_cb.cid AND yy=YEAR(NOW()) AND mm=MONTH(NOW()))
LEFT JOIN (SELECT tbl_ct.cid cid, tbl_tp.title tariff FROM contract_tariff tbl_ct JOIN tariff_plan tbl_tp ON (tbl_ct.tpid=tbl_tp.id AND (tbl_ct.date1<NOW() AND (tbl_ct.date2>NOW() OR tbl_ct.date2 IS NULL)))) tbl_tariff ON tbl_contract.id=tbl_tariff.cid
WHERE tbl_contract.id=$cid
";
                break;
            case 'services':
                $query = "
SELECT t_ist15.title type, t_id15.host host, t_is15.title title, t_idt15.title switch
FROM inet_serv_15 t_is15
LEFT JOIN inet_serv_type_15 t_ist15 ON t_is15.typeId=t_ist15.id
LEFT JOIN inet_device_tree_15 t_idt15 ON t_is15.deviceId=t_idt15.id
LEFT JOIN inv_device_15 t_id15 ON t_idt15.invDeviceId=t_id15.id
WHERE (t_is15.dateTo IS NULL OR t_is15.dateTo>=CURDATE()) AND t_is15.contractId=$cid
";
                break;

            default:
                break;
        }

        return static::sql($query);
    }

    /**
     * Get switch post address on map
     * @param string $ip
     * @return string
     */
    public function getSwitchAddress($ip){
        $query = "
SELECT t_eaa.value address
FROM inv_device_15 t_id15
LEFT JOIN entity_attr_address t_eaa ON (t_id15.entityId=t_eaa.entityId)
WHERE t_id15.host='$ip'
ORDER BY address DESC LIMIT 1
";

        $address = preg_replace("/^(\d{0,6})(, г\. Кумертау, )(.*)/", "$3", static::sql($query)->fetch_object()->address);

        return $address;
    }

    public static function getSwitchType($ip) {
        $query = "
SELECT title
FROM inv_device_15
WHERE host='$ip'
";
        $patterns = array('/Gray-IP-/', '/White-IP-/', '/[:-].*/');
        $replacements = '';
        $type = preg_replace($patterns, $replacements, self::sql($query)->fetch_object()->title);
        return $type;
    }
}