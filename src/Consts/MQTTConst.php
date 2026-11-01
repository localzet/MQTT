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
 * MQTTConst
 *
 * @package Localzet MQTT
 */
class MQTTConst
{
    const MQTT_PROTOCOL_LEVEL_3_1 = 3;

    const MQTT_PROTOCOL_LEVEL_3_1_1 = 4;

    const MQTT_PROTOCOL_LEVEL_5_0 = 5;

    const MQISDP_PROTOCOL_NAME = 'MQIsdp';

    const MQTT_PROTOCOL_NAME = 'MQTT';

    const MQTT_QOS_0 = 0;

    const MQTT_QOS_1 = 1;

    const MQTT_QOS_2 = 2;

    const MQTT_RETAIN_0 = 0;

    const MQTT_RETAIN_1 = 1;

    const MQTT_RETAIN_2 = 2;

    const MQTT_DUP_0 = 0;

    const MQTT_DUP_1 = 1;

    const MQTT_SESSION_PRESENT_0 = 0;

    const MQTT_SESSION_PRESENT_1 = 1;

    /**
     * CONNECT Packet.
     */
    const CMD_CONNECT = 1;

    /**
     * CONNACK
     */
    const CMD_CONNACK = 2;

    /**
     * PUBLISH
     */
    const CMD_PUBLISH = 3;

    /**
     * PUBACK
     */
    const CMD_PUBACK = 4;

    /**
     * PUBREC
     */
    const CMD_PUBREC = 5;

    /**
     * PUBREL
     */
    const CMD_PUBREL = 6;

    /**
     * PUBCOMP
     */
    const CMD_PUBCOMP = 7;

    /**
     * SUBSCRIBE
     */
    const CMD_SUBSCRIBE = 8;

    /**
     * SUBACK
     */
    const CMD_SUBACK = 9;

    /**
     * UNSUBSCRIBE
     */
    const CMD_UNSUBSCRIBE = 10;

    /**
     * UNSUBACK
     */
    const CMD_UNSUBACK = 11;

    /**
     * PINGREQ
     */
    const CMD_PINGREQ = 12;

    /**
     * PINGRESP
     */
    const CMD_PINGRESP = 13;

    /**
     * DISCONNECT
     */
    const CMD_DISCONNECT = 14;

    /**
     * Authentication exchange
     */
    const CMD_AUTH = 15;
}