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
 * Description of rSysLog
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class rSysLog {
    public static function getLog($switch) {
        $mysqli = new mysqli(RSYSLOG_HOST, RSYSLOG_USER, RSYSLOG_PASS, RSYSLOG_DB);
        if (mysqli_connect_errno()) {
            printf("Подключение к серверу MySQL невозможно. Код ошибки: %s\n", mysqli_connect_error());
            exit;
        }
        $mysqli->query("set character_set_client='utf8'");
        $mysqli->query("set character_set_results='utf8'");
        $mysqli->query("set collation_connection='utf8_general_ci'");
        $result = $mysqli->query("SELECT devicereportedtime, message FROM SystemEvents WHERE fromhost = '$switch' ORDER BY id DESC LIMIT 100");
        $tableRows = '';
        for($i = 0; $i < $result->num_rows; $i++){
            $rSysLog = $result->fetch_object();
            $tableRows = $tableRows."| $rSysLog->devicereportedtime | $rSysLog->message<br>";
        }

        return $tableRows;
    }
}
