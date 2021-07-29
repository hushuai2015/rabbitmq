<?php
namespace mq\consumer;

use app\model\MsgUser;
use mq\RabbitFacade;
use msg\gt\GtClient;
use think\Exception;
use think\facade\Cache;

class Msg implements ConsumerInterface
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
    protected function msgInsert(array $param,int $retry) :bool
    {

        try {

            $msgUserId = (new MsgUser())->_insert($param);
            if($msgUserId <= 0){
                return $this->error();
            }
            RabbitFacade::push([
                'pms'         => 'msgSender',
                'title'       => $param['title'],
                'content'     => $param['content'],
                'request_id'  => $param['request_id'],
                'receive_cid' => $param['receive_cid'],
                'msgUserId'   => $msgUserId,
            ],'msg_sender');
            return $this->complete();
        }catch (Exception $exception){
            $msg = 'Msg msgInsert Exception:'.$exception->getMessage();
            return $this->errorLogs($msg)->error();
        }

    }


    /**
     * @param array $param
     * @param int $retry
     * @return bool
     */
    protected function msgSender(array $param,int $retry) :bool
    {
        try {
            $gt    = new GtClient();
            $user  = new MsgUser();
            $where = ['id' => $param['msgUserId']];
            $res   = $gt->toSingleTran([$param['receive_cid']],[
                'title' => $param['title'],
                'body'  => $param['content']
            ]);
            if(isset($res['error'])){
                return $this->error();
            }
            $flag = $user->where($where)->force(true)->save(['is_send' => 1,'updated_time' => time()]);
            if(false === $flag){
                return $this->error();
            }
            return $this->complete();
        }catch (Exception $exception){
            $msg = 'Msg msgSender Exception:'.$exception->getMessage();
            return $this->errorLogs($msg)->error();
        }
    }
}