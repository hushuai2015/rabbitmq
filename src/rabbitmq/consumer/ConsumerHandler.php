<?php
namespace mq\consumer;



use mq\Config;
use mq\RabbitFacade;
use think\facade\Cache;
use think\facade\Log;

/**
 * 消费者辅助处理类
 * Trait ConsumerHandler
 * @package mq\consumer
 */
trait ConsumerHandler
{


    /**
     * @var null
     */
    protected $data = null;


    /**
     * @var string
     */
    protected $prefix = '';


    /**
     * @var string
     */
    protected $method = 'pms';

    /**
     * @var int
     */
    protected $count = 0;


    /**
     * 是否重新发布
     * @var bool
     */
    protected $isRelease = false;


    /**
     * @param array $params
     * @return ConsumerHandler
     */
    public function init(array $params): array
    {
        $this->data   = $params;
        $this->prefix = $params['queueConfig']['queue_name'];
        $this->count  = Cache::inc($this->cacheKey());
        return $this->data['params'];
    }


    /**
     * 完成消费
     * @return bool
     */
    public function complete()  :bool
    {
        Cache::delete($this->cacheKey());
        return true;
    }


    /**
     * @param string $message
     * @return $this
     */
    public function errorLogs(string  $message = '') : self
    {
        $message==='' ?: Log::error('date:' . date('Y-m-d H:i:s') . ' count: ' .$this->count. ' logsMsg: ' . $message);
        return $this;
    }


    /**
     * 返回错误
     * @return bool
     */
    public function error(): bool
    {
        if($this->count >= 2){
            Cache::delete($this->cacheKey());
            !$this->isRelease ?: RabbitFacade::push($this->data['params'],$this->data['queueConfig']['key']);
            return true;
        }
        return false;
    }


    /**
     * 是否重新发布
     * @return $this
     */
    public function release(): self
    {
        $this->isRelease = true;
        return $this;
    }


    /**
     * 延时
     * @param int $second 秒数
     * @return $this
     */
    public function sleep(int $second=1): self
    {
        $second <= 0 ?: sleep($second);
        return $this;
    }


    /**
     * @return string
     */
    protected function cacheKey(): string
    {
        return $this->prefix . '_' . $this->data['params'][$this->method];
    }



}
