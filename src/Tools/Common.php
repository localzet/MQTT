<?php

/**
 * @package     Localzet MQTT
 * @link        https://github.com/localzet/MQTT
 *
 * @author      Ivan Zorin <creator@localzet.com>
 * @copyright   Copyright © 2016-2026 Localzet Group. All rights reserved.
 * @license     https://www.gnu.org/licenses/agpl-3.0 GNU Affero General Public License v3.0
 *
 *              This program is free software: you can redistribute it and/or modify
 *              it under the terms of the GNU Affero General Public License as published
 *              by the Free Software Foundation, either version 3 of the License, or
 *              (at your option) any later version.
 *
 *              This program is distributed in the hope that it will be useful,
 *              but WITHOUT ANY WARRANTY; without even the implied warranty of
 *              MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *              GNU Affero General Public License for more details.
 *
 *              You should have received a copy of the GNU Affero General Public License
 *              along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 *              For any questions, please contact <creator@localzet.com>
*/


namespace localzet\MQTT\Tools;

/**
 * @method static string hexDump(string $encode)  // 以16进制显示
 * @method static string hexDumpAscii(string $encode)   // 以16进制和相应的ASCII字符显示
 * @method static string printableText(string $encode)  // 可打印字符
 * @method static string hexStream(string $encode)  // 16进制流
 * @method static string ascii(string $encode)  // 以ASCII字符显示
 */
abstract class Common
{
    public static function printf(string $data)
    {
        echo "\033[36m";
        for ($i = 0, $iMax = strlen($data); $i < $iMax; $i++) {
            $ascii = ord($data[$i]);
            if ($ascii > 31) {
                $chr = $data[$i];
            } else {
                $chr = ' ';
            }
            printf("%4d: %08b : 0x%02x : %d : %s\n", $i, $ascii, $ascii, $ascii, $chr);
        }
        echo "\033[0m";
    }

    public static function __callStatic($method, $arguments)
    {
        return (new Debug())->setEncode(...$arguments)->{$method}();
    }
}
