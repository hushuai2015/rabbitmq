<?php


namespace mq\consumer;
use app\service\Order;
use think\facade\Log;


/**
 * PMS 添加账务消费队列
 * Class PmsBill
 * @package mq\consumer
 */
class PmsBill
{
    /**
     * @param string $msg
     */
    public function job($msg)
    {
        $json = json_decode($msg, true);


    }


    /**
     * 金天鹅
     * @param array $param
     */
    protected function jte(array $param){
        $order  = new Order();
        $result = $order->addBillItemJte($param);
    }


    /**
     * 别样红
     * @param array $param
     */
    protected function byh(array $param){

    }


}