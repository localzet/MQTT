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


namespace localzet\MQTT\Handle\Property;

use localzet\MQTT\Consts\PropertyConst as Property;
use localzet\MQTT\Handle\DecodeTrait;

class UnPackProperty
{
    public static function connect(int $length, string &$body): array
    {
        $properties = [];
        do {
            $property = ord($body[0]);
            if (isset(PacketMap::$connect[$property])) {
                $key = PacketMap::$connect[$property];
                $body = substr($body, 1);
                switch ($property) {
                    case Property::SESSION_EXPIRY_INTERVAL:
                        $properties[$key] = DecodeTrait::longInt($body);
                        $length -= 5;
                        break;
                    case Property::AUTHENTICATION_METHOD:
                    case Property::AUTHENTICATION_DATA:
                        $properties[$key] = DecodeTrait::readString($body);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case Property::REQUEST_PROBLEM_INFORMATION:
                    case Property::REQUEST_RESPONSE_INFORMATION:
                        $properties[$key] = DecodeTrait::byte($body);
                        $length -= 2;
                        break;
                    case Property::RECEIVE_MAXIMUM:
                    case Property::TOPIC_ALIAS_MAXIMUM:
                    case Property::MAXIMUM_PACKET_SIZE:
                        $properties[$key] = DecodeTrait::shortInt($body);
                        $length -= 3;
                        break;
                    case Property::USER_PROPERTY:
                        $userKey = DecodeTrait::readString($body);
                        $userValue = DecodeTrait::readString($body);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($property);
                throw new \InvalidArgumentException("Property [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function willProperties(int $length, string &$body): array
    {
        $properties = [];
        do {
            $property = ord($body[0]);
            if (isset(PacketMap::$willProperties[$property])) {
                $key = PacketMap::$willProperties[$property];
                $body = substr($body, 1);
                switch ($property) {
                    case Property::MESSAGE_EXPIRY_INTERVAL:
                    case Property::WILL_DELAY_INTERVAL:
                        $properties[$key] = DecodeTrait::longInt($body);
                        $length -= 5;
                        break;
                    case Property::CONTENT_TYPE:
                    case Property::RESPONSE_TOPIC:
                    case Property::CORRELATION_DATA:
                        $properties[$key] = DecodeTrait::readString($body);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case Property::PAYLOAD_FORMAT_INDICATOR:
                        $properties[$key] = DecodeTrait::byte($body);
                        $length -= 2;
                        break;
                    case Property::USER_PROPERTY:
                        $userKey = DecodeTrait::readString($body);
                        $userValue = DecodeTrait::readString($body);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($property);
                throw new \InvalidArgumentException("Property [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function connAck(int $length, string &$body): array
    {
        $properties = [];
        do {
            $property = ord($body[0]);
            if (isset(PacketMap::$connAck[$property])) {
                $key = PacketMap::$connAck[$property];
                $body = substr($body, 1);
                switch ($property) {
                    case Property::SESSION_EXPIRY_INTERVAL:
                    case Property::MAXIMUM_PACKET_SIZE:
                        $properties[$key] = DecodeTrait::longInt($body);
                        $length -= 5;
                        break;
                    case Property::SERVER_KEEP_ALIVE:
                    case Property::RECEIVE_MAXIMUM:
                    case Property::TOPIC_ALIAS_MAXIMUM:
                        $properties[$key] = DecodeTrait::shortInt($body);
                        $length -= 3;
                        break;
                    case Property::ASSIGNED_CLIENT_IDENTIFIER:
                    case Property::AUTHENTICATION_METHOD:
                    case Property::AUTHENTICATION_DATA:
                    case Property::RESPONSE_INFORMATION:
                    case Property::SERVER_REFERENCE:
                    case Property::REASON_STRING:
                        $properties[$key] = DecodeTrait::readString($body);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case Property::MAXIMUM_QOS:
                    case Property::RETAIN_AVAILABLE:
                    case Property::WILDCARD_SUBSCRIPTION_AVAILABLE:
                    case Property::SUBSCRIPTION_IDENTIFIER_AVAILABLE:
                    case Property::SHARED_SUBSCRIPTION_AVAILABLE:
                        $properties[$key] = DecodeTrait::byte($body);
                        $length -= 2;
                        break;
                    case Property::USER_PROPERTY:
                        $userKey = DecodeTrait::readString($body);
                        $userValue = DecodeTrait::readString($body);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($property);
                throw new \InvalidArgumentException("Property [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function publish(int $length, string &$body): array
    {
        $properties = [];
        do {
            $property = ord($body[0]);
            if (isset(PacketMap::$publish[$property])) {
                $key = PacketMap::$publish[$property];
                $body = substr($body, 1);
                switch ($property) {
                    case Property::MESSAGE_EXPIRY_INTERVAL:
                        $properties[$key] = DecodeTrait::longInt($body);
                        $length -= 5;
                        break;
                    case Property::TOPIC_ALIAS:
                        $properties[$key] = DecodeTrait::shortInt($body);
                        $length -= 3;
                        break;
                    case Property::CONTENT_TYPE:
                    case Property::RESPONSE_TOPIC:
                    case Property::CORRELATION_DATA:
                        $properties[$key] = DecodeTrait::readString($body);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case Property::PAYLOAD_FORMAT_INDICATOR:
                        $properties[$key] = DecodeTrait::byte($body);
                        $length -= 2;
                        break;
                    case Property::USER_PROPERTY:
                        $userKey = DecodeTrait::readString($body);
                        $userValue = DecodeTrait::readString($body);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                    case Property::SUBSCRIPTION_IDENTIFIER:
                        $length -= 1;
                        $properties[$key] = DecodeTrait::varInt($body, $len);
                        $length -= $len;
                        break;
                }
            } else {
                $errType = dechex($property);
                throw new \InvalidArgumentException("Property [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function pubAndSub(int $length, string &$body): array
    {
        $properties = [];
        do {
            $property = ord($body[0]);
            if (isset(PacketMap::$pubAndSub[$property])) {
                $key = PacketMap::$pubAndSub[$property];
                $body = substr($body, 1);
                switch ($property) {
                    case Property::REASON_STRING:
                        $properties[$key] = DecodeTrait::readString($body);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case Property::USER_PROPERTY:
                        $userKey = DecodeTrait::readString($body);
                        $userValue = DecodeTrait::readString($body);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($property);
                throw new \InvalidArgumentException("Property [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function subscribe(int $length, string &$body): array
    {
        $properties = [];
        do {
            $property = ord($body[0]);
            if (isset(PacketMap::$subscribe[$property])) {
                $key = PacketMap::$subscribe[$property];
                $body = substr($body, 1);
                switch ($property) {
                    case Property::USER_PROPERTY:
                        $userKey = DecodeTrait::readString($body);
                        $userValue = DecodeTrait::readString($body);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                    case Property::SUBSCRIPTION_IDENTIFIER:
                        $length -= 1;
                        $properties[$key] = DecodeTrait::varInt($body, $len);
                        $length -= $len;
                        break;
                }
            } else {
                $errType = dechex($property);
                throw new \InvalidArgumentException("Property [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function unSubscribe(int $length, string &$body): array
    {
        $properties = [];
        do {
            $property = ord($body[0]);
            if (isset(PacketMap::$unSubscribe[$property])) {
                $body = substr($body, 1);
                switch ($property) {
                    case Property::USER_PROPERTY:
                        $userKey = DecodeTrait::readString($body);
                        $userValue = DecodeTrait::readString($body);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($property);
                throw new \InvalidArgumentException("Property [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function disConnect(int $length, string &$body): array
    {
        $properties = [];
        do {
            $property = ord($body[0]);
            if (isset(PacketMap::$disConnect[$property])) {
                $key = PacketMap::$disConnect[$property];
                $body = substr($body, 1);
                switch ($property) {
                    case Property::SESSION_EXPIRY_INTERVAL:
                        $properties[$key] = DecodeTrait::longInt($body);
                        $length -= 5;
                        break;
                    case Property::SERVER_REFERENCE:
                    case Property::REASON_STRING:
                        $properties[$key] = DecodeTrait::readString($body);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case Property::USER_PROPERTY:
                        $userKey = DecodeTrait::readString($body);
                        $userValue = DecodeTrait::readString($body);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($property);
                throw new \InvalidArgumentException("Property [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function auth(int $length, string &$body): array
    {
        $properties = [];
        do {
            $property = ord($body[0]);
            if (isset(PacketMap::$auth[$property])) {
                $key = PacketMap::$auth[$property];
                $body = substr($body, 1);
                switch ($property) {
                    case Property::AUTHENTICATION_METHOD:
                    case Property::AUTHENTICATION_DATA:
                    case Property::REASON_STRING:
                        $properties[$key] = DecodeTrait::readString($body);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case Property::USER_PROPERTY:
                        $userKey = DecodeTrait::readString($body);
                        $userValue = DecodeTrait::readString($body);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($property);
                throw new \InvalidArgumentException("Property [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }
}