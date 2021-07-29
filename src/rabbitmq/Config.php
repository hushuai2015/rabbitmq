<?php
namespace mq;
use mq\consumer\AppLogin;
use mq\consumer\Msg;
use mq\consumer\OrderPay;
use mq\consumer\SmsSend;
use mq\consumer\UploadCard;

/**
 * 配置
 * Class Rabbit
 * @package mq
 * @author hushuai
 */
trait Config
{


    /**
     * 连接信息
     */
    public $connect = [
        'host'     => '127.0.0.1',
        'port'     => '5672',
        'username' => 'admin',
        'password' => '123456',
        'vhost'    => '/'
    ];

    public $topic = [
        'exchange_name' => 'topic_exchange',
        'exchange_type' => 'topic',
        'queue_name'    => 'topic_queue',
        'route_key'     => '',
        'consumer_tag'  => 'topic'
    ];


    /**
     * 订单支付消费队列
     * @var string[]
     */
    public $order_pay = [
        'exchange_name' => 'topic_exchange',
        'exchange_type' => 'topic',
        'queue_name'    => 'order_pay_queue',
        'route_key'     => '*.order_pay',
        'consumer_tag'  => 'order_pay',
        'class_name'    => OrderPay::class,
        'no_ack'        => false  /* 是否自动确认消费【true是|false否】 */
    ];

    /**
     * 消息创建队列
     * @var string[]
     */
    public $msg_create = [
        'exchange_name' => 'topic_exchange',
        'exchange_type' => 'topic',
        'queue_name'    => 'msg_create_queue',
        'route_key'     => '*.msg_create',
        'consumer_tag'  => 'msg_create',
        'class_name'    => Msg::class,
        'no_ack'        => false  /* 是否自动确认消费【true是|false否】 */
    ];

    /**
     * 消息发送队列
     * @var string[]
     */
    public $msg_sender = [
        'exchange_name' => 'topic_exchange',
        'exchange_type' => 'topic',
        'queue_name'    => 'msg_sender_queue',
        'route_key'     => '*.msg_sender',
        'consumer_tag'  => 'msg_sender',
        'class_name'    => Msg::class,
        'no_ack'        => false  /* 是否自动确认消费【true是|false否】 */
    ];

    /**
     * APP登录后置操作队列
     * @var string[]
     */
    public $app_login = [
        'exchange_name' => 'topic_exchange',
        'exchange_type' => 'topic',
        'queue_name'    => 'app_login_queue',
        'route_key'     => '*.app_login',
        'consumer_tag'  => 'app_login',
        'class_name'    => AppLogin::class,
        'no_ack'        => false  /* 是否自动确认消费【true是|false否】 */
    ];

    /**
     * 身份证上传
     * @var string[]
     */
    public $upload_idcard = [
        'exchange_name' => 'topic_exchange',
        'exchange_type' => 'topic',
        'queue_name'    => 'upload_idcard_queue',
        'route_key'     => '*.upload_idcard',
        'consumer_tag'  => 'upload_idcard',
        'class_name'    => UploadCard::class,
        'no_ack'        => false  /* 是否自动确认消费【true是|false否】 */
    ];

    /**
     * 短信发送队列
     * @var string[]
     */
    public $sms_send = [
        'exchange_name' => 'topic_exchange',
        'exchange_type' => 'topic',
        'queue_name'    => 'sms_send_queue',
        'route_key'     => '*.sms_send',
        'consumer_tag'  => 'sms_send',
        'class_name'    => SmsSend::class,
        'no_ack'        => true  /* 是否自动确认消费【true是|false否】 */
    ];


}
