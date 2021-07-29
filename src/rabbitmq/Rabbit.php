<?php
namespace mq;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use think\facade\Cache;
use think\facade\Log;

/**
 * Class Rabbit
 * @package mq
 * @author hushuai
 */
class Rabbit
{

    /**
     * 发布消息
     * @param array $msg
     * @param string $topic
     * @return bool
     */
    public function push(array $msg,string $topic): bool
    {
        return (new Producer())->publish($msg,$topic);
    }


    /**
     * 消费消息
     * @param string $topic
     */
    public function consumer(string $topic):void
    {
        (new Consumer())->consumer($topic);
    }


    
}
