<?php

declare(strict_types=1);

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

namespace localzet\MQTT\Server\Protocols;

use InvalidArgumentException;
use localzet\MQTT\Protocols\Mqtt as Codec;
use localzet\Server\Connection\ConnectionInterface;
use localzet\Server\Protocols\ProtocolInterface;

final class Mqtt implements ProtocolInterface
{
    public static function input(string $buffer, ConnectionInterface $connection): int
    {
        return Codec::input($buffer);
    }

    public static function decode(string $buffer, ConnectionInterface $connection): mixed
    {
        return Codec::decode($buffer);
    }

    public static function encode(mixed $data, ConnectionInterface $connection): string
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException('MQTT protocol data must be an array.');
        }

        return Codec::encode($data);
    }
}
