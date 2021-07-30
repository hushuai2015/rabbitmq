<?php
namespace hs\rabbitmq;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use think\facade\Log;

/**
 * 消费者类
 * Class Consumer
 * @package hs\rabbitmq
 */
class Consumer extends RabbitBase
{



    public $class = null;
    protected $queueConfig = [];

    public function consumer($name)
    {

        //获取配置
        $this->queueConfig = $this->queue[$name];
        $this->queueConfig['key'] = $name;

        $this->class = $this->queueConfig['class_name'];

        //建立通道
        $channel = $this->connection->channel();

        //流量控制
        $channel->basic_qos(null, 1, null);

        //初始化交换机
        $channel->exchange_declare($this->topic['exchange_name'], $this->topic['exchange_type'], false, true, false);

        //初始化队列
        $channel->queue_declare($this->queueConfig['queue_name'], false, true, false, false);

        //绑定队列与交换机
        $channel->queue_bind($this->queueConfig['queue_name'], $this->topic['exchange_name'], $this->queueConfig['route_key']);

        /**
         * 消费消息
         * $queue 消息队列名称
         * $consumer_tag 消费者标签，用来区分多个消费者
         * $no_local 设置为true，表示 不能将同一个Conenction中生产者发送的消息传递给这个Connection中 的消费者
         * $no_ack 是否自动确认消息,true自动确认,false 不自动要手动调用,建立设置为false
         * $exclusive 是否排他
         */
        $channel->basic_consume(
            $this->queueConfig['queue_name'],
            $this->queueConfig['consumer_tag'],
            false,
            $this->queueConfig['no_ack'],
            false,
            false,
            [$this, 'handlerMsg']
        );

        //退出
        register_shutdown_function([$this, 'shutdown'], $channel, $this->connection);

        //监听
        while(count($channel->callbacks)) {
            $channel->wait();
        }

    }

    /**
     * @param $msg
     */
    public function handlerMsg($msg)
    {
        try {
            $data['params'] = json_decode($msg->body,true);
            $data['queueConfig'] = $this->queueConfig;
            $res = (new $this->class())->job($data,$msg->getDeliveryTag());
            if(false === $this->queueConfig['no_ack']){
                $res ? $msg->ack() : $msg->nack($msg->getDeliveryTag(),false,true);
            }
        }catch (\Exception $exception){  /* 程序出现异常自动消费 */
            Log::error('Comsumer handlerMsg Exception : ' . $exception->getMessage() . ' time: '.date('Y-m-d H:i:s'));
            $this->queueConfig['no_ack'] ?: $msg->ack();
        }


    }

    /**
     * 退出
     * @param  $channel [信道]
     * @param $connection [连接]
     */
    public function shutdown($channel, $connection)
    {
        $channel->close();
        $connection->close();
    }


}
