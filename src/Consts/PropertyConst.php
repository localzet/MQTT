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


namespace localzet\MQTT\Consts;

/**
 * @see https://docs.oasis-open.org/mqtt/mqtt/v5.0/os/mqtt-v5.0-os.html#_Toc3901029
 */
class PropertyConst
{
    const PAYLOAD_FORMAT_INDICATOR = 0x01;

    const MESSAGE_EXPIRY_INTERVAL = 0x02;

    const CONTENT_TYPE = 0x03;

    const RESPONSE_TOPIC = 0x08;

    const CORRELATION_DATA = 0x09;

    const SUBSCRIPTION_IDENTIFIER = 0x0B;

    const SESSION_EXPIRY_INTERVAL = 0x11;

    const ASSIGNED_CLIENT_IDENTIFIER = 0x12;

    const SERVER_KEEP_ALIVE = 0x13;

    const AUTHENTICATION_METHOD = 0x15;

    const AUTHENTICATION_DATA = 0x16;

    const REQUEST_PROBLEM_INFORMATION = 0x17;

    const WILL_DELAY_INTERVAL = 0x18;

    const REQUEST_RESPONSE_INFORMATION = 0x19;

    const RESPONSE_INFORMATION = 0x1A;

    const SERVER_REFERENCE = 0x1C;

    const REASON_STRING = 0x1F;

    const RECEIVE_MAXIMUM = 0x21;

    const TOPIC_ALIAS_MAXIMUM = 0x22;

    const TOPIC_ALIAS = 0x23;

    const MAXIMUM_QOS = 0x24;

    const RETAIN_AVAILABLE = 0x25;

    const USER_PROPERTY = 0x26;

    const MAXIMUM_PACKET_SIZE = 0x27;

    const WILDCARD_SUBSCRIPTION_AVAILABLE = 0x28;

    const SUBSCRIPTION_IDENTIFIER_AVAILABLE = 0x29;

    const SHARED_SUBSCRIPTION_AVAILABLE = 0x2A;
}
