<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2019-05-10
 * Time: 16:16
 */

namespace FirstW;




use FirstW\Client\DefaultClient;
use FirstW\client\DefaultTriggerClient;
use FirstW\Client\RedisClientInterface;

class TimerTask
{

    /**
     * @var RedisClientInterface
     */
    private $client = null;
    private $selectDB = 0;
    private $subKeys = [];
    private static $triggerClient;

    /** todo 订阅事件支持配置
     * TimerTask constructor.
     * @param $config
     * @param int $selectDb
     */
    public function __construct($config, $selectDb = 0)
    {
        /**
         * 消除前缀带来的影响
         */
        $this->client = new DefaultClient($config);
        self::$triggerClient = new DefaultTriggerClient($config);
        $this->selectDB = $selectDb;
        $this->client->select($selectDb);
        $this->checkNotificationStatus();
    }

    /**
     * @param int $time second
     * @param \Closure $callback
     */
    public function tick(int $time, \Closure $callback)
    {
        $this->client->psubscribe($this->subKeys, $this->recycle($time, $callback));
    }

    /**
     * @param $key
     * @param int $interval
     * @return bool
     */
    static public function trigger($key, $interval = 1)
    {
        if (self::$triggerClient->exists($key)) {
            return false;
        } else {
            return self::$triggerClient->set($key, time(), $interval);

        }

    }

    /**
     * @param $key
     * @return int
     */
    static public function stop($key)
    {
        return self::$triggerClient->del([$key]);
    }


    /**
     * @param $key
     * @return string
     */
    private function generatorChannel($key)
    {
        return sprintf('__keyspace@%d__:sf-prefix:%s', $this->selectDB, $key);
    }

    /**
     * @param array|string $patterns
     * @return TimerTask
     */
    public function subscribe(array $patterns)
    {
        if (is_array($patterns)) {
            foreach ($patterns as $pattern) {
                $this->subKeys[] = $this->generatorChannel($pattern);
            }
        } else {
            $this->subKeys[] = $patterns;
        }

        return $this;
    }

    /**
     * @param int $interval
     * @param \Closure $callback
     * @return \Closure
     */
    private function recycle(int $interval, \Closure $callback)
    {
        return function (\Redis $client, string $pattern, string $chan, string $msg) use ($interval, $callback) {
            if ($msg == 'expired') {
                $key = $this->getKey($chan);
                if (!$key || !call_user_func($callback, $pattern, $key, $msg)) {
                    return;
                }
                self::trigger($key, $interval);
            }

        };
    }

    /**
     * @param $chan
     * @return string
     */
    private function getKey($chan)
    {
        preg_match('/__keyspace@0__:sf-prefix:(.*)/', $chan, $key);
        return $key[1];
    }

    public function __destruct()
    {
        $this->client->punsubscribe($this->subKeys);
        $this->client->close();
    }

    /**
     * @return bool
     */
    private function checkNotificationStatus()
    {
        $configStr = $this->client->config('get', 'notify-keyspace-events', '');
        if (!strpos($configStr['notify-keyspace-events'], RedisKeyConstStore::KEY_SPACE) || !strpos($configStr['notify-keyspace-events'], RedisKeyConstStore::EVENT_EXPIRED)) {
            $this->client->config('set', 'notify-keyspace-events', RedisKeyConstStore::KEY_SPACE . RedisKeyConstStore::EVENT_EXPIRED);
        }
        return true;
    }
}