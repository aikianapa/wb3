<?php
//use Nahid\JsonQ\Jsonq;

class quotesClass extends cmsFormsClass {
    function beforeItemSave(&$item) {
        $item['login'] = $this->app->vars('_sess.user.login');
        isset($item['status']) ? null : $item['status'] = 'new';
    }


    function submit()
    {
        $res = [];
        // $item = $this->app->vars('_post');
        //$item = json_decode(file_get_contents('php://input'), true);
        // print(var_dump($item));
        $item=$_POST;
        $item['message'] = str_replace("\n", "<br>", $item['message']);
        $msg = $this->app->getTpl('feedback-mail.php');
        $msg->fetch($item);
        $subj = "Заявка с сайта ЮСАР";
        header('Content-Type: application/json; charset=utf-8');
        if ($item['email'] == '') {
            $res = ['error'=>true, 'msg'=>'*** Unknown error ***'];
            return json_encode($res);
        }

            $from = "mailer@".$this->app->route->domain;
            $sent = $this->app->vars('_sett.quote_email') > '' ? $this->app->vars('_sett.quote_email') : $this->app->vars('_sett.email');
            $res = $this->app->mail($from, $sent, $subj, $msg->outer(), $_FILES);

        //$item['_created'] = date('Y-m-d H:i:s');
        //$item = $this->app->itemSave('quotes', $item, true);

        $res = ['error'=>false,'item'=>$item];
        return json_encode($res);
    }

}

?>