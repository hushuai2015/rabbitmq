<?php
namespace hs\rabbitmq;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use think\facade\Config;

/**
 * Class Common
 * @package hs\rabbitmq
 */
class Common
{

    /**
     * @var array
     */
    protected static $conn = [];


    /**
     * @var AMQPStreamConnection 
     */
    protected $connection;


    /**
     * @var array
     */
    protected $topic;

    /**
     * 队列信息
     * @var array
     */
    protected $queue;


    /**
     * Common constructor.
     */
    public function __construct(){

        $config  = Config::get('rabbitmq');
        $connect = $config['connect'];
        $this->topic = $config['topic'];
        $this->queue = $config['queue'];
        $this->connection = new AMQPStreamConnection($connect['host'], $connect['port'], $connect['username'],$connect['password']);
    }


    /**
     * job方法
     * 传入一个字符串参数，返回布尔类型，true 为确认消费 false 未确认消费
     * @param array $msg
     * @param int $retry
     * @return bool
     */
    public function job(array $msg,int $retry) :bool
    {
        $func  = $msg['params']['pms'];
        $class = $msg['queueConfig']['class_name'];
        return method_exists($class,$func) ? (new $class())->$func($msg,$retry) : true;
    }

}
