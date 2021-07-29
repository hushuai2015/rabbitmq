<?php
namespace mq\consumer;
use app\model\OrderPayRecord;
use app\service\Machine;
use app\service\Order;
use GuzzleHttp\Exception\GuzzleException;
use think\Exception;
use think\exception\ErrorException;
use think\facade\Cache;
use think\facade\Log;

class UploadCard implements ConsumerInterface
{

    use ConsumerHandler;

    /**
     * @param array $msg
     * @param int $retry
     * @return bool
     */
    public function job(array $msg,int $retry): bool
    {
        $data = $this->init($msg);
        $func = !isset($data[$this->method]) ?: $data[$this->method];
        return method_exists(self::class,$func) ? $this->$func($data,$retry) : true;
    }


    /**
     * @param array $param
     * @return bool
     */
    public function upload(array $param): bool
    {
        try{
            $result = (new Machine())->uploadIdCard($param);
            return $result ? $this->complete() : $this->error();
        }catch (Exception $exception){
            $msg = 'UploadCard.upload.Exception:'.$exception->getMessage();
            return $this->errorLogs($msg)->complete();
        }
    }


}