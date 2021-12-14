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
 * Description of Core
 *
 * @author Sergey Ilyin <developer@ilyins.ru>
 */
class Core {
    public static function timeticksConvert($timeticks) {
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

    public static function cleanSNMPValue($value) {
        $patterns = array('/IpAddress: /', '/INTEGER: /', '/Gauge32: /', '/Hex-STRING: /', '/STRING: /', '/Timeticks: \(\d*\) /');
        #return str_replace(' ', '-', preg_replace($patterns, '', $value));
        return preg_replace($patterns, '', $value);
    }
}
