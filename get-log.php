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

include_once 'config.php';

spl_autoload_register(function ($class) {
    switch ($class) {

        default:
            include __DIR__."/classes/$class.class.php";
            break;
    }
});

$inputRequestData = filter_input_array(INPUT_POST) ?? filter_input_array(INPUT_GET);

echo 'LOG коммутатора ' . $inputRequestData['host'] . ':<br>' . rSysLog::getLog($inputRequestData['host']);
