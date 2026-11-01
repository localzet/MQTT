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

namespace localzet\MQTT\Protocols;

use localzet\MQTT\Consts\MQTTConst;
use localzet\MQTT\Handle\DecodeTrait;
use localzet\MQTT\Handle\DecodeV5;
use localzet\MQTT\Handle\EncodeTrait;
use localzet\MQTT\Handle\EncodeV5;

/**
 * Mqtt5 Protocol.
 *
 * @author    Ivan Zorin
 */
class Mqtt5
{
    /**
     * Check the integrity of the package.
     *
     * 如果可以在$buffer中得到请求包的长度则返回整个包的长度
     * 否则返回0，表示需要更多的数据才能得到当前请求包的长度
     * 如果返回false或者负数，则代表错误的请求，则连接会断开
     * @param string $buffer
     * @return int
     */
    public static function input(string $buffer)
    {
        $length = strlen($buffer);
        $body_length = DecodeTrait::getBodyLength($buffer, $head_bytes);
        $total_length = $body_length + $head_bytes;
        if ($length < $total_length) {
            return 0;
        }

        return $total_length;
    }

    /**
     * 用于请求打包
     *
     * 当需要向客户端发送数据即调用$connection->send($data);时
     * 会自动把$data用encode打包一次，变成符合协议的数据格式，然后再发送给客户端
     * 也就是说发送给客户端的数据会自动encode打包，无需业务代码中手动调用
     * @param array $data
     * @return string
     */
    public static function encode(array $data)
    {
        $cmd = $data['cmd'];
        switch ($cmd) {
            // ['cmd'=>1, 'clean_session'=>x, 'will'=>['qos'=>x, 'retain'=>x, 'topic'=>x, 'content'=>x],'username'=>x, 'password'=>x, 'keepalive'=>x, 'protocol_name'=>x, 'protocol_level'=>x, 'client_id' => x]
            case MQTTConst::CMD_CONNECT:
                $package = EncodeV5::connect($data);
                break;
            //['cmd'=>2, 'session_present'=>0/1, 'code'=>x]
            case MQTTConst::CMD_CONNACK:
                $package = EncodeV5::connAck($data);
                break;
            // ['cmd'=>3, 'message_id'=>x, 'topic'=>x, 'content'=>x, 'qos'=>0/1/2, 'dup'=>0/1, 'retain'=>0/1]
            case MQTTConst::CMD_PUBLISH:
                $package = EncodeV5::publish($data);
//                var_dump((new Debug($package))->hexDumpAscii());
                break;
            // ['cmd'=>x, 'message_id'=>x]
            case MQTTConst::CMD_PUBACK:
            case MQTTConst::CMD_PUBREC:
            case MQTTConst::CMD_PUBREL:
            case MQTTConst::CMD_PUBCOMP:
                $package = EncodeV5::genReasonPhrase($data);
                break;
            // ['cmd'=>8, 'message_id'=>x, 'topics'=>[topic=>qos, ..]]]
            case MQTTConst::CMD_SUBSCRIBE:
                $package = EncodeV5::subscribe($data);
                break;
            // ['cmd'=>9, 'message_id'=>x, 'codes'=>[x,x,..]]
            case MQTTConst::CMD_SUBACK:
                $package = EncodeV5::subAck($data);
                break;
            // ['cmd' => 10, 'message_id' => $message_id, 'topics' => $topics, 'properties'=>[]];
            case MQTTConst::CMD_UNSUBSCRIBE:
                $package = EncodeV5::unSubscribe($data);
                break;
            // ['cmd'=>11, 'message_id'=>x, 'properties'= []]
            case MQTTConst::CMD_UNSUBACK:
                $package = EncodeV5::unSubAck($data);
                break;
            // ['cmd'=>x]
            case MQTTConst::CMD_PINGREQ;
            case MQTTConst::CMD_PINGRESP:
                $package = EncodeTrait::packHead($cmd, 0);
                break;
            case MQTTConst::CMD_DISCONNECT:
                $package = EncodeV5::disconnect($data);
                break;
            case MQTTConst::CMD_AUTH:
                $package = EncodeV5::auth($data);
                break;
            default:
                throw new \InvalidArgumentException('MQTT Type not exist');
        }

        return $package;
    }

    /**
     * 用于请求解包
     *
     * input返回值大于0，并且Localzet Server收到了足够的数据，则自动调用decode
     * 然后触发onMessage回调，并将decode解码后的数据传递给onMessage回调的第二个参数
     * 也就是说当收到完整的客户端请求时，会自动调用decode解码，无需业务代码中手动调用
     * @param string $buffer
     * @return array
     */
    public static function decode(string $buffer)
    {
//        var_dump((new Debug($buffer))->hexDumpAscii());
        $cmd = DecodeTrait::getCmd($buffer);
        $body = DecodeTrait::getBody($buffer);
        switch ($cmd) {
            // ['cmd'=>1, 'clean_session'=>x, 'will'=>['qos'=>x, 'retain'=>x, 'topic'=>x, 'content'=>x],'username'=>x, 'password'=>x, 'keepalive'=>x, 'protocol_name'=>x, 'protocol_level'=>x, 'client_id' => x]
            case MQTTConst::CMD_CONNECT:
                $package = DecodeV5::connect($body);
                break;
            case MQTTConst::CMD_CONNACK:
                $package = DecodeV5::connAck($body);
                break;
            case MQTTConst::CMD_PUBLISH:
                $dup = ord($buffer[0]) >> 3 & 0x1;
                $qos = ord($buffer[0]) >> 1 & 0x3;
                $retain = ord($buffer[0]) & 0x1;
                $package = DecodeV5::publish($dup, $qos, $retain, $body);
                break;
            case MQTTConst::CMD_PUBACK:
            case MQTTConst::CMD_PUBREC:
            case MQTTConst::CMD_PUBREL:
            case MQTTConst::CMD_PUBCOMP:
                $package = DecodeV5::getReasonCode($cmd, $body);
                break;
            case MQTTConst::CMD_PINGREQ:
            case MQTTConst::CMD_PINGRESP:
                $package = ['cmd' => $cmd];
                break;
            case MQTTConst::CMD_DISCONNECT:
                $package = DecodeV5::disconnect($body);
                break;
            case MQTTConst::CMD_SUBSCRIBE:
                $package = DecodeV5::subscribe($body);
                break;
            case MQTTConst::CMD_SUBACK:
                $package = DecodeV5::subAck($body);
                break;
            case MQTTConst::CMD_UNSUBSCRIBE:
                $package = DecodeV5::unSubscribe($body);
                break;
            case MQTTConst::CMD_UNSUBACK:
                $package = DecodeV5::unSubAck($body);
                break;
            case MQTTConst::CMD_AUTH:
                $package = DecodeV5::auth($body);
                break;
            default:
                $package = [];
        }
        return $package;
    }
}
