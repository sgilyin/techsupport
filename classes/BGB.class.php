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
    public static function getData($cid) {
        $mysqli = new mysqli(BGB_HOST, BGB_USER, BGB_PASS, BGB_DB);
        if (mysqli_connect_errno()) {
            printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
            exit;
        }
        $mysqli->query("set character_set_client='utf8'");
        $mysqli->query("set character_set_results='utf8'");
        $mysqli->query("set collation_connection='utf8_general_ci'");
        $bgb_result = $mysqli->query("SELECT CONCAT(tbl_street.title, ' д. ', tbl_house.house, CONCAT_WS( ' кв. ',tbl_house.frac, IF(tbl_flat.flat='',NULL,tbl_flat.flat))) address, tbl_cpt1.val phone, tbl_id15.host, tbl_idps15.port, IF(tbl_contract.status=0,'Активен','Не активен') status, tbl_cb.summa1+tbl_cb.summa2-tbl_cb.summa3-tbl_cb.summa4 balance, tbl_tp.title tariff, tbl_ist15.title type
FROM contract tbl_contract
LEFT JOIN contract_parameter_type_2 tbl_flat ON (tbl_contract.id=tbl_flat.cid)
LEFT JOIN address_house tbl_house ON (tbl_flat.hid=tbl_house.id)
LEFT JOIN address_street tbl_street ON (tbl_house.streetid=tbl_street.id)
LEFT JOIN contract_parameter_type_1 tbl_cpt1 ON (tbl_contract.id=tbl_cpt1.cid AND tbl_cpt1.pid=2)
LEFT JOIN inet_serv_15 tbl_is15 ON (tbl_contract.id=tbl_is15.contractId)
LEFT JOIN inv_device_port_subscription_15 tbl_idps15 ON (tbl_idps15.subscriberId=tbl_is15.id)
LEFT JOIN inv_device_15 tbl_id15 ON (tbl_id15.id=tbl_idps15.deviceId)
LEFT JOIN contract_balance tbl_cb ON (tbl_contract.id=tbl_cb.cid AND yy=YEAR(NOW()) AND mm=MONTH(NOW()))
LEFT JOIN contract_tariff tbl_ct ON (tbl_contract.id=tbl_ct.cid AND (tbl_ct.date1<NOW() AND (tbl_ct.date2>NOW() OR tbl_ct.date2 IS NULL)))
LEFT JOIN tariff_plan tbl_tp ON (tbl_ct.tpid=tbl_tp.id)
LEFT JOIN inet_serv_type_15 tbl_ist15 ON (tbl_ist15.id=tbl_is15.typeId)
WHERE tbl_contract.id=".$cid)->fetch_object();

        return $bgb_result;
    }
}
