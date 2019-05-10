<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-05-10
 * Time: 16:33
 */

namespace FirstW;


final class RedisKeyConstStore
{
    const KEY_SPACE = 'K';
    const KEY_EVENT = 'E';

    const ALL = 'A';
    //type  command  char
    const TYPE_STRING = '$';
    const TYPE_LIST = 'l';
    const TYPE_SET = 's';
    const TYPE_HASH = 'h';
    const TYPE_ZSET = 'z';
    const TYPE_GENERIC= 'g';
    //event command char
    const EVENT_EXPIRED = 'x';
    const EVENT_EVICT = 'e';// deleted when memory is greater than the limitation


}