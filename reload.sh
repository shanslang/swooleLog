echo "loading..."
pid=`pidof php`
echo $pid
kill -USR1 $pid
echo "loading success"
