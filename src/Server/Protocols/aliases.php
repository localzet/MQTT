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
 */

$aliases = [
    \localzet\Server\Protocols\Mqtt::class => \localzet\MQTT\Server\Protocols\Mqtt::class,
    \localzet\Server\Protocols\Mqtt5::class => \localzet\MQTT\Server\Protocols\Mqtt5::class,
];

foreach ($aliases as $source => $alias) {
    if (!class_exists($alias, false)) {
        class_alias($source, $alias);
    }
}
