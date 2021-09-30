<?php

class modChat
{
    protected $clients;

    public function __construct($obj = null)
    {
        if (is_object($obj) && is_a($obj, 'wbApp')) {
            $this->app = &$obj;
            $this->dom = $this->app->fromString('');
        } else if (is_object($obj) && is_a($obj, 'wbDom')) {
            $this->dom = &$obj;
            $this->app = &$obj->app;
        } 

        $this->server();

        $username = trim($this->app->vars('_sess.user.first_name'). ' ' . $this->app->vars('_sess.user.last_name'));
        $avatar = $this->app->vars('_sess.user.avatar.0');

        $this->json = (object)[
            'server' => $this->app->route->host,
            'project' => 'yonger',
            'room' => '',
            'sender' => [
                'id' => $this->app->vars('_sess.user.id'),
                'name' => $username,
                'avatar' => $avatar
            ],
            'receiver' => '',
            'msg' => '',
            'attach' => '',
            'command' => ''
        ];

        if (isset($this->dom)) {
            $mode = $this->app->vars('_route.mode');
            if (method_exists($this, $mode)) {
                echo $this->$mode();
            }
        } 
    }

    public function init() {
            $ui = $this->app->fromFile(__DIR__."/chat_ui.php", true);
            $ui->fetch();
            $ui->append("<script type='text/json' id='schema'>".json_encode($this->json)."</script>");
            return $ui;
    }

    public function join() {
        $app = &$this->app;
        header("Content-type: application/json; charset=utf-8");
        $msg = $app->vars('_post');
        $msg['msg'] = 'id' . md5($msg['server'] . $msg['project'] . $msg['room']);
        return json_encode($msg);
    }

    public function getRooms() {
        $app = &$this->app;
        header("Content-type: application/json; charset=utf-8");
        $project = $app->vars('_post.project');
        $rooms = ['yonger' => ['test','general','yonger']];
        $msg = $this->json;
        $msg->msg = $rooms[$project];
        $msg->command = 'setrooms';
        return json_encode($msg);
    }

    public function message() {
        $app = &$this->app;
        $msg = $app->vars('_post');
        $msg = $app->itemInit('messages',$msg);
        return json_encode($msg);

    }

    public function direct() {
        echo 1234;
    }

    public function server() {
        @$res = exec('php '. __DIR__ .'/chatserver.php status');
        if (strpos($res,'not run')) {
            @exec('php '. __DIR__ .'/chatserver.php start -d');
        } else {
            // ChatServer is running;
        }
    }

}