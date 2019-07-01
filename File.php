<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Clog;

use Swoole\Coroutine as co;
//define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
//define('DS', DIRECTORY_SEPARATOR);
/**
 * 本地化调试输出到文件
 */
class File
{

    protected $config = [
        'time_format' => ' c ',
        'file_size'   => 2097152,
        'path'        => __DIR__.'/client_log/',
        'apart_level' => [],
    ];

    protected $writed = [];

    // 实例化并传入参数
    public function __construct($config = [])
    {
        if (is_array($config)) {
            $this->config = array_merge($this->config, $config);
        }
    }

    /**
     * 日志写入接口
     * @access public
     * @param array $log 日志信息
     * @return bool
     */
    public function save(array $log = [])
    {
        $cli         = IS_CLI ? '_cli' : '';
        $destination = $this->config['path'] . date('Ym') . DS . date('d') . $cli . '.log';
        if(isset($this->config['finame'])){
            $destination = $this->config['path'] . date('Y-m-d') . DS . $this->config['finame'] . '.log';
        }

        $path = dirname($destination);
        !is_dir($path) && mkdir($path, 0755, true);

        $info = '';
        foreach ($log as $type => $val) {
            $level = '';
            $level .= '[ ' . $type . ' ] ' . $val . "\r\n";
           // foreach ($val as $msg) {
               // if (!is_string($msg)) {
                   // $msg = var_export($msg, true);
               // }
               // $level .= '[ ' . $type . ' ] ' . $msg . "\r\n";
            //}
           // if (in_array($type, $this->config['apart_level'])) {
                // 独立记录的日志级别
                //$filename = $path . DS . date('d') . '_' . $type . $cli . '.log';
               // $this->write($level, $filename, true);
           // } else {
                $info .= $level;
            //}
        }
       // $this->writeslog('save info= '.$info);
        if ($info) {
            return $this->write($info, $destination);
        }
        return true;
    }

    protected function write($message, $destination, $apart = false)
    {
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        //if (is_file($destination) && floor($this->config['file_size']) <= filesize($destination)) {
        //rename($destination, dirname($destination) . DS . time() . '-' . basename($destination));
        //$this->writed[$destination] = false;
        // }
		 $now = date('Y-m-d H:i:s');
        if (empty($this->writed[$destination]) && !IS_CLI) {
            //$now     = date($this->config['time_format']);
            $now = date('Y-m-d H:i:s');
            $server  = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '0.0.0.0';
            $remote  = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
            $method  = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
            $uri     = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $message = "---------------------------------------------------------------\r\n[{$now}] {$server} {$remote} {$method} {$uri}\r\n" . $message;

            $this->writed[$destination] = true;
        }
		$message = "---------------------------------------------------------------\r\n[{$now}] \r\n" . $message;
        //if (IS_CLI) {
            //$now     = date($this->config['time_format']);
            //$message = "[{$now}]" . $message;
        //}

       // return error_log($message, 3, $destination); 

          $fp = fopen($destination, "a+");
          co::create(function () use ($fp, $message)
          {
              $r =  co::fwrite($fp, $message);
             // $this->writeslog('task msg= '.$message);
              return $r;
          });
          //go(function() use($fp){  // 使用匿名函数
            //  $r =  co::fwrite($fp, $message);
             // return $r;
          //});
    }
  
    public function writeslog($log){
        if(is_array($log))
        {
            $log = json_encode($log, JSON_UNESCAPED_UNICODE);
        }
        $log_path = 'sql_log/'.date('Y-m-d',time()).'-sql_log.txt';
        $ts = fopen($log_path,"a+");
        fputs($ts,date('Y-m-d H:i:s',time()).'  '.$log."\r\n");
        fclose($ts);
    }

}
