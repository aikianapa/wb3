<?php
class ctrlEngine
{
    public function __construct(&$app)
    {
        $mode = $app->vars("_route.mode");
        try {
            $app->dom = $this->$mode($app);
        } catch (Exception $err) {
        }
    }

    public function modules(&$app)
    {
        $uri = $app->vars("_srv.REQUEST_URI");
        $params = explode("?",$uri);
        $script = $params[0];
        include_once($_ENV["path_app"].$script);
    }
}
