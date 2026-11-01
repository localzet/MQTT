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


namespace localzet\MQTT\Handle;

trait EncodeTrait
{
    /**
     * Pack string.
     *
     * @param string $str
     * @return string
     */
    public static function packString(string $str): string
    {
        $len = strlen($str);
        return pack('n', $len) . $str;
    }

    public static function stringPair(string $key, string $value): string
    {
        return static::packString($key) . static::packString($value);
    }

    public static function longInt(int $int): string
    {
        return pack('N', $int);
    }

    public static function shortInt(int $int): string
    {
        return pack('n', $int);
    }

    public static function varInt(int $int): string
    {
        return static::writeBodyLength($int);
    }

    /**
     * packHead.
     *
     * @param int $cmd
     * @param int $body_length
     * @param int $dup
     * @param int $qos
     * @param int $retain
     * @return string
     */
    public static function packHead(int $cmd,
                                    int $body_length,
                                    int $dup = 0, int $qos = 0, int $retain = 0): string
    {
        $cmd = $cmd << 4;
        if ($dup) {
            $cmd |= 1 << 3;
        }
        if ($qos) {
            $cmd |= $qos << 1;
        }
        if ($retain) {
            $cmd |= 1;
        }
        return chr($cmd) . static::writeBodyLength($body_length);
    }

    /**
     * Write body length.
     *
     * @param int $len
     * @return string
     */
    protected static function writeBodyLength(int$len): string
    {
        $string = '';
        do {
            $digit = $len % 128;
            $len = $len >> 7;
            // if there are more digits to encode, set the top bit of this digit
            if ($len > 0) {
                $digit = ($digit | 0x80);
            }
            $string .= chr($digit);
        } while ($len > 0);

        return $string;
    }
}