<?php
namespace mq\consumer;
use app\model\OrderPayRecord;
use app\service\Order;
use GuzzleHttp\Exception\GuzzleException;
use think\Exception;
use think\exception\ErrorException;
use think\facade\Cache;
use think\facade\Log;

class OrderPay implements ConsumerInterface
{
    use ConsumerHandler;


    /**
     * @param string $msg
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
     * 别样红
     * 1、查询入住人信息。
     * 2、添加账务（主入住人）。
     * 3、修改在线支付记录
     * @param array $param
     * @param int $retry
     * @return bool
     */
    protected function byh(array $param,int $retry): bool
    {

        try{
            $order    = new Order();
            $attach   = $param['attach'];
            $checkins = $order->queryCheckins($attach['hotel_id'],$attach['order_id']);

            if(empty($checkins)){
                return $this->sleep(2)->error();
            }
            $attach['billId'] = $checkins[0]['BillId'];
            $record = (new OrderPayRecord())->where(['pay_no' => $param['pay_no']])->field('id,price,attach,is_bill,is_update,status')->findOrEmpty();
            if($record->isEmpty() || $record->status == 2){  /* 没有支付记录，移出队列 */
                return $this->complete();
            }

            $recordAttach = json_decode($record->attach,true);
            $attach['amount'] = $record->price;
            $attach['onlinePaymentId'] = $param['pay_no'];
            if($record->is_bill == 2){
                $bill_id = $order->addBillItem($param['pay_no'],$attach);  /*添加账务*/
            }else{
                $bill_id = $recordAttach['billItemId'] ?? '';
            }
            if($record->is_update == 2 && $bill_id != ''){
                $order->updateOnlinePayment($bill_id,$attach); /*修改在线支付记录*/
                return $this->complete();
            }
            return $this->error();
        }catch (GuzzleException $guzzleException){
            $msg = 'byh.GuzzleException:'.$guzzleException->getMessage();
            return $this->errorLogs($msg)->error();
        }catch (Exception $exception){
            $msg = 'byh.Exception:'.$exception->getMessage();
            return $this->errorLogs($msg)->error();
        }
    }

    /**
     * 别样红会员余额抵扣房费，扣除pms 会员余额
     * @param array $param
     * @param int $retry
     * @return bool
     */
    protected function byhMember(array $param,int $retry): bool
    {
        $param['pay_no'] = $param['order_id'];
        try{
            Log::debug('byhMember.params:'.var_export($param,true));
            $order = new Order();
            $param['amount']      = $param['price'];
            $param['subItemType'] = 'C9130';
            $param['billIType']   = 'Credit';
            $res = $order->byhMember($param);
            if(!$res){
               return $this->error();
            }

            $checkins = $order->queryCheckins($param['hotel_id'],$param['order_id']);
            if(empty($checkins)){
                return $this->error();
            }
            $param['billId'] = $checkins[0]['BillId'];
            $order->addBillItem($param['pay_no'] ?? '',$param,false);  /*添加账务*/

            return $this->complete();
        }catch (GuzzleException $guzzleException){
            $msg = 'byhMember.GuzzleException:'.$guzzleException->getMessage();
            return $this->errorLogs($msg)->complete();
        }catch (Exception $exception){
            $msg = 'byhMember.Exception:'.$exception->getMessage();
            return $this->errorLogs($msg)->complete();
        }

    }


    /**
     * @param array $param
     * @param int $retry
     */
    protected function jte(array $param,int $retry): bool
    {
        try{
            $order = new Order();
            $bill  = $order->addBillItemJte($param);
            if($bill == ''){
                return $this->error();
            }

            $flag  = false;
            $count = 1;
            while (1){
                $payStatus = $order->getPayStatusJte($param['hotel_id'],$bill);
                if($payStatus['data']['PayBoxResult'] == -1 || $payStatus['data']['PayBoxResult'] == 1){
                    $flag = true;
                    break;
                }elseif($payStatus['data']['PayBoxResult'] == 2){
                    if($count >= 3){
                        break;
                    }
                    sleep(1);
                    $count ++ ;
                }else{
                    break;
                }
            }

            return $flag?$this->$this->complete():$this->error();
        }catch (GuzzleException $guzzleException){
            $msg = 'jte.GuzzleException:'.$guzzleException->getMessage();
            return $this->errorLogs($msg)->error();
        }catch (Exception $exception){
            $msg = 'jte.Exception:'.$exception->getMessage();
            return $this->errorLogs($msg)->error();
        }
    }

}