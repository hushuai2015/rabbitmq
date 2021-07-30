<?php

namespace hs\rabbitmq;


/**
 * 消费接口,消费类必须实现job方法
 * 传入一个字符串参数，返回布尔类型，true 为确认消费 false 未确认消费
 * Interface ConsumerInterface
 * @package hs\rabbitmq
 */
interface ConsumerInterface
{

    /**
     * @param array $msg  消息内容
     * @param int $retry   重试次数
     * @return bool
     */
    public function job(array $msg,int $retry) :bool ;




}