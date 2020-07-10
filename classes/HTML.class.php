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
 * Class for HTML
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class HTML {
    public static function getIdForm($cid) {
        $html = "<div class='entry'><div class='woo-sc-box normal rounded full'>
            <form method='post'>
            <label for='cid'>ID договора в Биллинге</label>
            <input type='text' name='cid' id='cid' value='$cid'>
            <input type='submit' name='Submit' value='Собрать данные'>
            <a href='https://fialka.tv/tech'> Сбросить</a>
            </div></div>";

        return $html;
    }

    public static function getContractInfo($bgb_result) {
        $html = "<div class='entry'><div class='woo-sc-box normal rounded full'>
            Адрес: $bgb_result->address<br>
            Статус договора: $bgb_result->status<br>
            Тарифный план: $bgb_result->tariff<br>
            Баланс: $bgb_result->balance (~".static::getCountDays($bgb_result->tariff, $bgb_result->balance)." дней до блокировки)
            </div></div>";

        return $html;
    }

    public static function getSwitchInfo($bgb_result, $edgeCoreData) {
        $html = "<div class='entry'><div class='woo-sc-box normal  rounded full'>
            Коммутатор: $bgb_result->host Uptime: $edgeCoreData->sysUpTime
            </div></div>";

        return $html;
    }

    public static function getPortInfo($bgb_result, $edgeCoreData) {
        switch ($edgeCoreData->ifOperStatus){
            case 1:
                $ifOperStatus = 'Есть';
                break;
            default:
                $ifOperStatus = 'Нет';
                break;
        }

        for ($i=0; $i<count($edgeCoreData->dhcpSnoopBindingsIpAddress); $i++){
            if (intval($bgb_result->port)<25 && intval(substr(strrchr($edgeCoreData->dhcpSnoopBindingsIpAddress[$i], "."), -1))<5){
                if (intval(substr(strrchr($edgeCoreData->dhcpSnoopBindingsIpAddress[$i], "."), 1, -1)) == $bgb_result->port){
                    $ip = $ip.$edgeCoreData->dhcpSnoopBindingsIpAddress[$i].' ('.date("H:i:s", $edgeCoreData->dhcpSnoopBindingsLeaseTime[$i]).')<br>';
                }
            }
            if (intval($bgb_result->port)>24 && intval(substr(strrchr($edgeCoreData->dhcpSnoopBindingsIpAddress[$i], "."), -1))>4){
                if (intval(substr(strrchr($edgeCoreData->dhcpSnoopBindingsIpAddress[$i], "."), 1, -1))+24 == $bgb_result->port){
                    $ip = $ip.$edgeCoreData->dhcpSnoopBindingsIpAddress[$i].' ('.date("H:i:s", $edgeCoreData->dhcpSnoopBindingsLeaseTime[$i]).')<br>';
                }
            }
        }

        for ($i=0; $i<count($edgeCoreData->macs); $i++){
            if ($edgeCoreData->macPorts[$i] == $bgb_result->port){
                $macDashed = str_replace(' ', '-', substr($edgeCoreData->macs[$i], 0, -1));
                $mac = $mac."$macDashed (". static::getMacVendor($macDashed).')<br>';
            }
        }

        $html = "<div class='entry'><div class='woo-sc-box normal  rounded full'>
            Порт: $bgb_result->port Актив: $ifOperStatus ($edgeCoreData->ifLastChange)<br>
            Download: $edgeCoreData->portOutUtil Mbps Upload: $edgeCoreData->portInUtil Mbps (300 sec.)<br>
            IP-адреса:<br>$ip
            MAC-адреса:<br>$mac
            </div></div>";

        return $html;
    }

    public static function getCableTestInfo($edgeCoreData) {
        $html = "<div class='entry'><div class='woo-sc-box normal  rounded full'>
            Замер кабеля на $edgeCoreData->cableDiagResultTime:<br>
            1 пара: ".$edgeCoreData->cableDiagResultStatusPairA->status." ($edgeCoreData->cableDiagResultDistancePairA). ".$edgeCoreData->cableDiagResultStatusPairA->hint."<br>
            2 пара: ".$edgeCoreData->cableDiagResultStatusPairB->status." ($edgeCoreData->cableDiagResultDistancePairB). ".$edgeCoreData->cableDiagResultStatusPairB->hint."<br>
            <form method='post'>
            <input type='submit' name='btnCableTest' value='Выполнить замер'>
            </div></div>";

        return $html;
    }

    public static function getSwitchLog($switch) {
        $tableRows = EdgeCore::getLog($switch);
        $html = "<div class='entry'><div class='woo-sc-box normal  rounded full'>
            LOG коммутатора $switch:<br>
            $tableRows
            </div></div>";

        return $html;
    }
    public static function getBitrixForm($bgb_result) {
        $html = "<div class='entry'><div class='woo-sc-box normal  rounded full'>
            <form method='post'>
            <input type='hidden' name='bx[address]' value='$bgb_result->address'>
            <input type='hidden' name='bx[phone]' value='$bgb_result->phone'>
            <table>
            <tbody>
                <tr>
                    <td>Тип задачи</td>
                </tr>
                <tr>
                    <td>
                        <input type='radio' name='bx[type]' value='ЗК' id='bxTypeZK'>
                        <label for='bxTypeZK'>ЗК</label>
                        <input type='radio' name='bx[type]' value='СКНП' id='bxTypeSKNP'>
                        <label for='bxTypeSKNP'>СКНП</label>
                        <input type='radio' name='bx[type]' value='Speedtest' id='bxTypeSpeedtest'>
                        <label for='bxTypeSpeedtest'>Speedtest</label>
                        <input type='radio' name='bx[type]' value='Роутер' id='bxTypeRouter'>
                        <label for='bxTypeRouter'>Настройка роутера</label>
                    </td>
                </tr>
                <tr>
                    <td>Половина дня</td>
                </tr>
                <tr>
                    <td>
                        <input type='radio' name='bx[halfDay]' value='9' id='bxHalfDay1'>
                        <label for='bxHalfDay1'>1</label>
                        <input type='radio' name='bx[halfDay]' value='14' id='bxHalfDay2'>
                        <label for='bxHalfDay2'>2</label>
                    </td>
                </tr>
                <tr>
                    <td>Дата</td>
                </tr>
                <tr>
                    <td>
                        <input type='date' name='bx[date]'>
                        <label for='preCall'>Предварительный звонок</label>
                        <input type='checkbox' name='bx[preCall]' id='preCall'>
                        <input type='text' name='bx[minPreCall]' id='minPreCall'>
                        <label for='minPreCall'>минут</label>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for='description'>Дополнительный комментарий, если необходим</label>
                        <input type='text' name='bx[description]' id='description'>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type='submit' name='createBxTask' value='Создать задачу в Битрикс'>
                        <input type='reset'>
                    </td>
                </tr>
            </tbody>
            </table>
            </form>
            </div></div>";

        return $html;
    }

    private function getCountDays($tariff, $balance) {
        switch ($tariff) {
            case '2018 Активный (25М/300Р) - Архив 2018':
                $cost = 250;
                break;

            default:
                $cost = 300;
                break;
        }

        return intdiv($balance, $cost/intval(date("t")));
    }

    private function getMacVendor($mac) {
        $url = 'https://2ip.ua/ru/services/information-service/mac-find';
        $post['a'] = 'act';
        $post['mac'] = $mac;
        $response = cURL::executeRequest($url, $post, false);
        preg_match('/Имя компании:.*\n\t{5}<td>.*<\/td>/m', $response, $vendor);

        return substr(strrchr($vendor[0], ":"), 16);
    }
}
