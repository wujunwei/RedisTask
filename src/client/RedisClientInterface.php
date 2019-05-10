<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-05-10
 * Time: 16:38
 */

namespace FirstW\Client;


interface RedisClientInterface
{
    /**
     * Client constructor.
     * @param $config
     * @example
     * $redis= new client('127.0.0.1', 6379);
     * $redis= new client('127.0.0.1');                 // port 6379 by default - same connection like before.
     * $redis= new client('127.0.0.1', 6379, 2.5);      // 2.5 sec timeout and would be another connection than the two before.
     * $redis= new client('127.0.0.1', 6379, 2.5, 'x'); // x is sent as persistent_id and would be another connection than the three before.
     * $redis= new client('/tmp/redis.sock');           // unix domain socket - would be another connection than the four before.
     */
    public function __construct($config);

    /**
     * Disconnects from the Redis instance, except when pconnect is used.
     */
    public function close();

    /**
     * Get or Set the redis config keys.
     *
     * @param   string $operation either `GET` or `SET`
     * @param   string $key for `SET`, glob-pattern for `GET`. See https://redis.io/commands/config-get for examples.
     * @param   string $value optional string (only for `SET`)
     * @return  array   Associative array for `GET`, key -> value
     * @link    https://redis.io/commands/config-get
     * @link    https://redis.io/commands/config-set
     * @example
     * <pre>
     * $redis->config("GET", "*max-*-entries*");
     * $redis->config("SET", "dir", "/var/run/redis/dumps/");
     * </pre>
     */
    public function config($operation, $key, $value);

    /**
     * Subscribe to channels by pattern
     *
     * @param   array $patterns The number of elements removed from the set.
     * @param   string|array $callback Either a string or an array with an object and method.
     *                          The callback will get four arguments ($redis, $pattern, $channel, $message)
     * @link    https://redis.io/commands/psubscribe
     * @example
     * <pre>
     * function psubscribe($redis, $pattern, $chan, $msg) {
     *  echo "Pattern: $pattern\n";
     *  echo "Channel: $chan\n";
     *  echo "Payload: $msg\n";
     * }
     * </pre>
     */
    public function pSubscribe($patterns, $callback);

    /**
     * unSubscribe to channels by pattern
     *
     * @param $key
     * @link    https://redis.io/commands/punSubscribe
     */
    public function punSubscribe($key);

    /**
     * Switches to a given database.
     *
     * @param   int $dbindex
     * @return  bool    TRUE in case of success, FALSE in case of failure.
     * @link    https://redis.io/commands/select
     * @example
     * <pre>
     * $redis->select(0);       // switch to DB 0
     * $redis->set('x', '42');  // write 42 to x
     * $redis->move('x', 1);    // move to DB 1
     * $redis->select(1);       // switch to DB 1
     * $redis->get('x');        // will return 42
     * </pre>
     */
    public function select($dbindex);
}