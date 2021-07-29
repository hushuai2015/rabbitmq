<?php
namespace mq\consumer;

use app\model\HotelAccount;
use app\model\MsgUser;
use mq\RabbitFacade;
use msg\gt\GtClient;
use think\Exception;
use think\facade\Cache;

class AppLogin implements ConsumerInterface
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
     * @param int $retry
     * @return bool
     */
    protected function appLogins(array $param,int $retry) :bool
    {
        try {
            $account = new HotelAccount();
            $result  = $account->where('id','=',$param['id'])->force(true)->save([
                'cid'        => $param['cid'] ?? '',
                'login_time' => $param['time'],
                'login_ip'   => $param['login_ip']
            ]);
            if(false === $result){
                return $this->error();
            }
            return $this->complete();
        }catch (Exception $exception){
            $msg = 'appLogins Exception :' . $exception->getMessage();
            return $this->errorLogs($msg)->error();
        }

    }


}