<?php
/*
php chatserver.php start
php chatserver.php start -d -демонизировать скрипт
php chatserver.php status
php chatserver.php stop
php chatserver.php restart
php chatserver.php restart -d
php chatserver.php reload
*/

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/ObsceneCensorRus.php';

use Workerman\Worker;

$context = array(
    'ssl' => array(
        'local_cert'  => '/your/path/of/server.pem',
        'local_pk'    => '/your/path/of/server.key',
        'verify_peer' => false,
    )
);
$worker = new Worker("websocket://0.0.0.0:8000", $context);
// $worker->transport = 'ssl';
$chat = new chat($worker);
$worker->count = 1;
$worker->chat = &$chat;
$worker->channels = [];

$worker->onWorkerStart = function ($worker) {
    echo "Worker starting...\n";
};

$worker->onWorkerStop = function ($worker) {
    echo "Worker stopped...\n";
};


$worker->onConnect = function ($connection) {
    echo "New connection\n";
};

$worker->onMessage = function ($connection, $data) {
    $worker = &$connection->worker;
    $chat = $worker->chat;
    $data = $chat->msgRouter($data, $connection->id);
    echo "{$data}\n";
};

$worker->onClose = function ($connection) {
    $connection->send($connection->id);

    echo "Connection closed\n";
};

// Run worker
Worker::runAll();


class chat
{
    /**
     * Class constructor.
     */
    public function __construct($worker)
    {
        $this->worker = $worker;
/*      $this->clients = new \SplObjectStorage;
        $this->clients->attach($conn);
        $this->clients->detach($conn);
*/
    }

    function newId()
    {
        $mt = explode(' ', microtime());
        $md = substr(str_repeat('0', 2).dechex(ceil($mt[0] * 10000)), -4);
        $id = 'id'.dechex(time() + rand(100, 999));
        return $id;
    }

    public function channelId($msg) {
        $msg = (array)$msg;
        $id = 'id' . md5($msg['server'] . $msg['project'] . $msg['room']);
        return $id;
    }

    public function channelUsers($chid) {
        $worker = &$this->worker;
        $connections = &$worker->connections;
        $users = [];
        foreach ($connections as $connection) {
            $user = &$connection->user;
            in_array($chid, $connection->channels) ? $users[$user->id] = $user : null;
        }
        return $users;
    }

    public function msgRouter($data, $cid)
    {   
        $worker = &$this->worker;
        $data = json_decode($data);
        $command = $data->command;
        $server = $data->server;
        $msg = $data;
        $connections = &$worker->connections;
        $connection = &$connections[$cid];
        $connection->user = $data->sender;
        !isset($connection->channels) ? $connection->channels = [] : null;
        switch ($data->command) {
            case 'join': // Вход в комнату
                $chid = $this->channelId($msg);
                $msg->msg = $chid;
                !in_array($chid, $connection->channels) ? $connection->channels[] = $chid : null;
                $msg = json_encode($msg);
                $connection->send($msg);
                $umsg = $data;
                $umsg->command = 'channelusers';
                $umsg->msg = $this->channelUsers($chid);
                $umsg->room = $chid;
                $umsg = json_encode($umsg);
                foreach($connections as $conn) {
                    // обновляем список пользователей в комнате
                    in_array($chid, (array)$conn->channels) ? $conn->send($umsg) : null;
                }
                break;
            case 'leave': // Выход из комнаты
                $msg->msg = $this->channelId($msg);
                if (in_array($msg->msg, $connection->channels)) {
                    $k = array_flip($connection->channels);
                    unset($connection->channels[$k[$msg->msg]]);
                } 
                $msg = json_encode($msg);
                $connection->send($msg);
                break;
            case 'message': // обработка сообщений
                $channel = $this->channelId($msg);
                $msg->_created = date('Y-m-d H:i:s');
                $msg->id = $this->newId();
                $msg->msg = ObsceneCensorRus::getFiltered($msg->msg);

                foreach ($connections as $conn) {
                    if ($msg->room > "") {
                        if (in_array($channel, $connection->channels)) {
                            $conn->send(json_encode($msg));
                        }
                    } else {

                    }
                }


                //$msg = $this->ajax($server.'/module/chat/'.$command, $msg);
                $msg = json_encode($msg);
                break;
            default:
                $msg = $this->ajax($server.'/module/chat/'.$command, $msg);
                $connection->send($msg);


                break;
        }



        return $msg;
    }

    public function ajax($url, $post=null, $username=null, $password=null)
    {
        if (func_num_args()==3) {
            $password=$username;
            $username=$get;
            $post=array();
        }
        !is_array($post) ? $post=(array)$post : null;

        $cred = sprintf('Authorization: Basic %s', base64_encode("$username:$password"));
        $post=http_build_query($post);
        $opts = array(
                'http'=>array(
                    'method'=>'POST',
                    'header'=>$cred
                    ."\r\nCookie: ".session_name()."=".session_id()
                    ."\r\nContent-Length: ".strlen($post)
                    ."\r\nContent-Type: application/x-www-form-urlencoded",
                    'content'=>$post
                ),
                 "ssl"=>array(
                     "verify_peer"=>false,
                     "verify_peer_name"=>false,
                 )
            );
        $context = stream_context_create($opts);
        session_write_close();
        $result = file_get_contents($url, false, $context);
        return $result;
    }
}
