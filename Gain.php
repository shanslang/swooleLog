<?php
/**
 * CreateTime: 2019/6/29 9:38
 * Author: hhh
 * Description:
 */
require_once  'File.php';
use Clog\File;

class Gain
{
    protected $_key = 'FFDFEE8B01CF3D7109DDB4909BCC8266';

    public function clientLog($response)
    {
            //$this->writeslog($response);
      		$arrtwo = json_decode($response, true);
            if(!$arrtwo){
      			$this->writeslog('未传参');
                return 'sss';
      		}
        
            if(!array_key_exists('data', $arrtwo) || !array_key_exists('sign', $arrtwo)){
                $this->writeslog('缺少参数');
                return '缺少参数';
            }

            $data = $arrtwo['data'];
            $sign = $arrtwo['sign'];

            $isSign   = $this->checkSign($data, $sign);
            if($isSign == 1)
            {
                $this->writeslog('签名 失败');
                return '签名 失败';
            }

            $data2 = base64_decode($data);

            $arrs = json_decode($data2, true);
           // $arrs2 = json_encode($arrs, JSON_UNESCAPED_UNICODE);

            $arrs['userid']  = $arrs['userid'] ?? 0;
            $arrs['version']  = $arrs['version'] ?? 100;
            $path = $arrs['userid'].'-'. $arrs['version'] .'-'.date('Y-m-d');
            $config = [
                'type'   =>  'File',
                'single' => true,
                'path'   =>  __DIR__.'/client_log/',
                'finame'   => $path,
            ];
            $file = new File($config);
            $logdata = $arrs['logdata'];
            array_pop($arrs);
            $str = json_encode($arrs, JSON_UNESCAPED_UNICODE).$logdata;
            $logarr['client'] = $str;
//            $file->save($str, 'client');
            $file->save($logarr);
            return 'success';
    }

    public function checkSign($data,$sign)
    {
        $key = $this->_key;
        $strings = trim($key.$data);
        $md = strtoupper(md5($strings));
        if($sign != $md)
        {
            return 1;
        }else
        {
            return 2;
        }
    }

    public function writeslog($log){
        if(is_array($log))
        {
            $log = json_encode($log, JSON_UNESCAPED_UNICODE);
        }
        $log_path = __DIR__.'/sql_log/'.date('Y-m-d',time()).'-sql_log.txt';
        $ts = fopen($log_path,"a+");
        fputs($ts,date('Y-m-d H:i:s',time()).'  '.$log."\r\n");
        fclose($ts);
    }
}
