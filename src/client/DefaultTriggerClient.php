<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-05-10
 * Time: 17:38
 */

namespace FirstW\client;


class DefaultTriggerClient implements RedisTriggerClientInterface
{
    private $client;

    /**
     * DefaultClient constructor.
     * @param $config
     * @example
     * $redis= new client('127.0.0.1', 6379);
     * $redis= new client('127.0.0.1');                 // port 6379 by default - same connection like before.
     * $redis= new client('127.0.0.1', 6379, 2.5);      // 2.5 sec timeout and would be another connection than the two before.
     * $redis= new client('127.0.0.1', 6379, 2.5, 'x'); // x is sent as persistent_id and would be another connection than the three before.
     * $redis= new client('/tmp/redis.sock');           // unix domain socket - would be another connection than the four before.
     */
    public function __construct($config)
    {
        $client = new \Redis();
        if(!$client->pconnect(...$config)){
            $error = $client->getLastError() ?? 'can not connect to the redis server.';
            throw new \RuntimeException($error);
        }
        $this->client = $client;
    }
    /**
     * Disconnects from the Redis instance, except when pconnect is used.
     */
    public function close()
    {
        $this->client->close();
    }

    /**
     * Verify if the specified key/keys exists.
     *
     * @param   string|string[] $key
     * @return  int    The number of keys tested that do exist
     * @link    https://redis.io/commands/exists
     * @ling    https://github.com/phpredis/phpredis#exists
     * @example
     * <pre>
     * $redis->set('key', 'value');
     * $redis->exists('key'); // 1
     * $redis->exists('NonExistingKey'); // 0
     *
     * $redis->mset(['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz']);
     * $redis->exists(['foo', 'bar', 'baz]); // 3
     * $redis->exists('foo', 'bar', 'baz'); // 3
     * </pre>
     *
     * This function took a single argument and returned TRUE or FALSE in phpredis versions < 4.0.0.
     */
    public function exists($key)
    {
        return $this->client->exists($key);
    }

    /**
     * Set the string value in argument as value of the key.
     *
     * @param   string $key
     * @param   string $value
     * @param   int|array $timeout [optional] Calling setex() is preferred if you want a timeout.<br>
     *                      Since 2.6.12 it also supports different flags inside an array. Example ['NX', 'EX' => 60]<br>
     *                      EX seconds -- Set the specified expire time, in seconds.<br>
     *                      PX milliseconds -- Set the specified expire time, in milliseconds.<br>
     *                      PX milliseconds -- Set the specified expire time, in milliseconds.<br>
     *                      NX -- Only set the key if it does not already exist.<br>
     *                      XX -- Only set the key if it already exist.<br>
     * @return  bool    TRUE if the command is successful.
     * @link    https://redis.io/commands/set
     * @example $redis->set('key', 'value');
     */
    public function set($key, $value, $timeout = 0)
    {
        return $this->client->set($key, $value, $timeout);
    }


    /**
     * Remove specified keys.
     *
     * @param array $key
     * @return  int             Number of keys deleted.
     * @link    https://redis.io/commands/del
     * @example
     * <pre>
     * $redis->set('key1', 'val1');
     * $redis->set('key2', 'val2');
     * $redis->set('key3', 'val3');
     * $redis->set('key4', 'val4');
     * $redis->delete('key1', 'key2');          // return 2
     * $redis->delete(array('key3', 'key4'));   // return 2
     * </pre>
     */
    public function del( ...$key)
    {
        return $this->client->del(func_get_args());
    }
}