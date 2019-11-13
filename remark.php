先平滑启动：nohup /www/server/php/72/bin/php  /www/wwwroot/Clog/HttpClog.php  >  /www/wwwroot/Clog/HttpClog.txt  &
服务，再通过nginx代理，要建client_log文件夹

location / {
        index index.html index.htm;     
        proxy_pass http://127.0.0.1:8501;
    }
