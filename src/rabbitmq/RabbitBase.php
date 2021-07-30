<?php
namespace hs\rabbitmq;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use think\facade\Config;

/**
 * 基类
 * Class RabbitBase
 * @package hs\rabbitmq
 */
class RabbitBase
{


    /**
     * 连接句柄
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
     * RabbitBase constructor.
     */
    public function __construct(){

        $config  = Config::get('rabbitmq');
        $connect = $config['connect'];
        $this->topic = $config['topic'];
        $this->queue = $config['queue'];
        $this->connection = new AMQPStreamConnection($connect['host'], $connect['port'], $connect['username'],$connect['password']);
    }

}
