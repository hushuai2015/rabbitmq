<?php
namespace hs\rabbitmq\consumer;

use hs\rabbitmq\ConsumerHandler;
use hs\rabbitmq\ConsumerInterface;

class SmsSend implements ConsumerInterface
{
    use ConsumerHandler;

    /**
     * @param array $msg
     * @param int $retry
     * @return bool
     */
    public function job(array $msg, int $retry): bool
    {
        $data = $this->init($msg);
        $func = !isset($data[$this->method]) ?: $data[$this->method];
        return method_exists(self::class,$func) ? $this->$func($data,$retry) : true;
    }


    /**
     * @param array $param
     * @param int $retry
     * @return bool
     */
    public function test(array $param,int $retry): bool
    {
        echo $retry . "\n" ;
        sleep(1);
        return $this->release()->error();
    }
}