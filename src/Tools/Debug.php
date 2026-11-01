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

class Debug
{
    private $encode;

    public function __construct(string $encode = '')
    {
        $this->encode = $encode;
    }

    public function setEncode(string $encode): self
    {
        $this->encode = $encode;

        return $this;
    }

    public function getEncode(): string
    {
        return $this->encode;
    }

    public function hexDump(): string
    {
        return $this->toHexDump($this->getEncode());
    }

    public function hexDumpAscii(): string
    {
        return $this->toHexDump($this->getEncode(), true);
    }

    public function ascii(): string
    {
        return $this->toAscii($this->getEncode());
    }

    public function printableText(): string
    {
        return $this->getEncode();
    }

    public function hexStream(): string
    {
        return bin2hex($this->getEncode());
    }

    private function toHexDump(string $contents, bool $hasAscii = false): string
    {
        $address = $column = 0;
        $result = $hexDump = $asciiDump = '';

        $sprintf = '%08x    %-48s';

        if ($hasAscii) {
            $sprintf = '%08x    %-48s   %s';
        }

        foreach (str_split($contents) as $c) {
            $hexDump = $hexDump . sprintf('%02x ', ord($c));
            if ($hasAscii) {
                if (ord($c) > 31 && ord($c) < 128) {
                    $asciiDump .= $c;
                } else {
                    $asciiDump .= '.';
                }
            }
            $column++;
            if (($column % 16) == 0) {
                $line = sprintf($sprintf, $address, $hexDump, $asciiDump);
                $result .= $line . PHP_EOL;

                $asciiDump = '';
                $hexDump = '';
                $column = 0;
                $address += 16;
            }
        }

        if ($column > 0) {
            $line = sprintf($sprintf, $address, $hexDump, $asciiDump);
            $result .= $line;
        }

        return $result;
    }

    private function toAscii(string $contents): string
    {
        $address = $column = 0;
        $result = $asciiDump = '';

        $sprintf = '%08x    %s';

        foreach (str_split($contents) as $c) {
            if (ord($c) > 31 && ord($c) < 128) {
                $asciiDump .= $c;
            } else {
                $asciiDump .= '.';
            }

            $column++;
            if (($column % 16) == 0) {
                $line = sprintf($sprintf, $address, $asciiDump);
                $result .= $line . PHP_EOL;

                $asciiDump = '';
                $column = 0;
                $address += 16;
            }
        }

        if ($column > 0) {
            $line = sprintf($sprintf, $address, $asciiDump);
            $result .= $line;
        }

        return $result;
    }
}
