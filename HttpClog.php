<?php
/**
 * CreateTime: 2019/6/29 17:09
 * Author: hhh
 * Description:
 */
header('content-type:text/html;charset=utf-8');
use Swoole\Http\Server;
class HttpClog
{
    CONST HOST = '0.0.0.0';
    CONST PORT = 8501;

    public $http = null;

    public function __construct()
    {
        $this->http = new Server(self::HOST,self::PORT);

        $this->http->set([
            'worker_num'				=> 8,
            'task_worker_num'			=> 8,
    		'task_enable_coroutine'     => true,   // 开启任务协程
        ]);

        $this->http->on("request",[$this, 'onRequest']);
        $this->http->on('task',[$this, 'onTask']);
        $this->http->on('finish',[$this, 'onFinish']);
        $this->http->on('close', [$this, 'onClose']);
        $this->http->start();
    }

    public function onRequest($request, $response)
    {
        //print_r($request->rawContent());  
        $this->http->task($request->rawContent());   // 投递任务
        $response->end('success');
    }
  
    // 处理任务
    public function onTask($serv, Swoole\Server\Task $task)
    {
        // 任务
        require_once  'Gain.php';
        $hh = new Gain();
      	define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
		define('DS', DIRECTORY_SEPARATOR);
    
        $hhs = $hh->clientLog($task->data);
        $task->finish($hhs);
    }

    // 任务处理完成，调用或return时触发
    public function onFinish($ws, $task_id, $data)
    {
        //echo 'task send info ='.$data.' time end= '.date('Y-m-d H:i:s');
      echo 'task end';
       //print_r($data);
    }

    public function onClose(swoole_server $server, int $fd, int $reactorId)
    {
        echo $fd.PHP_EOL;
    }
}

new HttpClog();