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
 * Class for Bitrix24
 *.
 * @author Sergey Ilyin <developer@ilyins.ru>
 */

class BX24 {
    /**
     * Execute method on Bitrix24
     *.
     * @param String $bx24Method
     * @param Array $bx24Data
     * @return json
     */
    private function callMethod($bx24Method, $bx24Data) {

        $url = CRM_HOST.'/rest/1/'.CRM_SECRET."/$bx24Method";
        $result = cURL::executeRequest($url, $bx24Data, FALSE);

        return $result;
    }

    /**
     * Get params for task in Bitrix24
     *.
     * @param String $type
     * @return Object
     */
    private function getParams($type) {
        $btrx = new stdClass();
        switch ($type) {
            case 'faultEth':
                $btrx->responsible_id = 18;// Ответственный 12 - Саня, 18 - Женя
                $btrx->accomplices = array(1);// Соисполнители
                $btrx->auditors = array(668,6768);// Наблюдатели
                $btrx->tags = array('Неисправность','Ethernet', 'Заявка', 'Internet');// Теги задачи
                $btrx->group_id = 16;// Группа "Неисправности"
                $btrx->pid = 43;// Поле задачи в Биллинге
                break;
        }

        return $btrx;
    }

    /**
     * Get phone link for task in Bitrix24
     * 
     * @param String $phonesString
     * @return String
     */
    private function getPhoneLink($phonesString) {
        $phonesArray = explode(",", preg_replace('/[^0-9,]/', '', $phonesString));
        for ($i = 0; $i < count($phonesArray); $i++) {
            $result .= "<a href='tel:$phonesArray[$i]'>$phonesArray[$i]</a>, ";
        }
        return $result;
    }

    public static function createTask($cid, $bx24Data, $bgb_result, $edgeCoreData) {
        switch (get_current_user_id()) {
            case 27:
                $task['fields']['CREATED_BY'] = 12;
                break;
            case 3432:
                $task['fields']['CREATED_BY'] = 8;
                break;
            case 4159:
                $task['fields']['CREATED_BY'] = 14;
                break;
            case 4160:
                $task['fields']['CREATED_BY'] = 18;
                break;
            case 4161:
                $task['fields']['CREATED_BY'] = 16;
                break;

            default:
                $task['fields']['CREATED_BY'] = 1;
                break;
        }

        $btrx = static::getParams('faultEth');

        switch (intval($bx24Data['halfDay'])) {
            case 14:
                $halfDay = 2;
                break;

            default:
                $halfDay = 1;
                break;
        }

        switch ($bx24Data['preCall']) {
            case 'on':
                $preCall = " (за ".$bx24Data['minPreCall']." минут)";
                break;

            default:
                $preCall = '';
                break;
        }

        $cableTestInfo = "Замер кабеля на $edgeCoreData->cableDiagResultTime:<br>"
                . "1 пара: ".$edgeCoreData->cableDiagResultStatusPairA->status." ($edgeCoreData->cableDiagResultDistancePairA). ".$edgeCoreData->cableDiagResultStatusPairA->hint."<br>"
                . "2 пара: ".$edgeCoreData->cableDiagResultStatusPairB->status." ($edgeCoreData->cableDiagResultDistancePairB). ".$edgeCoreData->cableDiagResultStatusPairB->hint;

        $phones = static::getPhoneLink($bgb_result->phone);

        $task['fields']['TITLE'] = "$halfDay | Eth | ".$bx24Data['type']." | ".$bx24Data['address'].$preCall;
        $task['fields']['RESPONSIBLE_ID'] = $btrx->responsible_id;
        $task['fields']['ACCOMPLICES'] = $btrx->accomplices;
        $task['fields']['AUDITORS'] = $btrx->auditors;
        $task['fields']['TAGS'] = $btrx->tags;
        $task['fields']['GROUP_ID'] = $btrx->group_id;
        $task['fields']['ALLOW_CHANGE_DEADLINE'] = 'Y';
        $task['fields']['DEADLINE'] = date('c',strtotime($bx24Data['date'].' '.$bx24Data['halfDay'].':00:00 + 4 hour'));
        $task['fields']['DESCRIPTION'] = "ID договора в Биллинге: <a href='https://fialka.tv/tech?cid=$cid'>$cid</a><br>Телефоны: $phones<br><br>$cableTestInfo<br><br>".$bx24Data['description'];
        $task['fields']['START_DATE_PLAN'] = date('c',strtotime($bx24Data['date'].' '.$bx24Data['halfDay'].':00:00'));
        $task['fields']['END_DATE_PLAN'] = date('c',strtotime($task['fields']['START_DATE_PLAN'].'+ 3 hour'));    

        return json_decode(static::callMethod('tasks.task.add.json', http_build_query($task)));
    }
}
