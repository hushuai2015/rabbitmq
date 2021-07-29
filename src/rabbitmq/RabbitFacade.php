<?php
namespace mq;

use think\Facade;

/**
 * Class RabbitFacade
 * @package mq
 * @author hushuai
 * @mixin Rabbit
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
