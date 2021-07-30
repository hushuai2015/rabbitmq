<?php
namespace hs\rabbitmq;

use think\Facade;

/**
 * 门面
 * Class RabbitFacade
 * @package hs\rabbitmq
 * @method static mixed push(array $msg,string $topic)
 * @method static mixed consumer(string $topic)
 */
class RabbitFacade extends Facade
{
    protected static function getFacadeClass()
    {
        return Rabbit::class;
    }
}
