<?php
class ctrlForm {
    function __construct( $app ) {
        $this->app = $app;
        $this->route = $app->route;
        $mode = $this->route->mode;
        $this->$mode();
    }

    function __call( $mode, $params ) {
        if ( !is_callable( @$this->$mode ) ) {
            header( 'HTTP/1.1 404 Not Found' );
            echo 'Error 404';
            die;
        }
    }

    function show() {
        $app = &$this->app;
        $cache = $app->getCache();
        if (!$cache) {
            if ( isset( $this->route->form ) ) {
                $dom = $app->getForm( $this->route->form, $this->route->mode );
            }
            if ( isset( $this->route->item ) ) {
                $table = $this->route->form;
                if ( isset( $this->route->table ) ) $table = $this->route->table;
                $item = $app->db->itemRead( $table, $this->route->item );
                if ( isset( $item['template'] ) AND $item['template'] > '' AND $item['active'] == 'on' ) {
                    $dom = $app->getTpl( $item['template'] );
                } else if ( isset( $this->route->tpl ) ) {
                    $dom = $app->getTpl( $this->route->tpl );
                } else {
                    header( 'HTTP/1.1 404 Not Found' );
                    $dom = $app->getTpl( '404.php' );
                    if ( !$dom ) $dom = $app->fromString( "<html><head><meta name='viewport' content='width=device-width; initial-scale=1.0; user-scalable=no'></head><center><img src='/engine/modules/cms/tpl/assets/img/virus.svg' width='200'><h3>[404] Page not found</h3></center></html>" );
                }
                if ( $dom ) $dom->item = $item;
            }
            $dom->fetch();
            $out = $dom->outer();
            if ( !strpos( ' '.$out, '<!DOCTYPE html>' ) ) $out = '<!DOCTYPE html>'.$out;
            echo $out;
            $app->setCache($out);
        } else {
          echo $cache;
        }
        die;
    }

    function ajax() {
        $app = $this->app;
        $form = $app->vars( '_route.params.0' );
        $mode = $app->vars( '_route.params.1' );
        if ( $mode == 'list' AND $app->vars( '_post.render' ) == 'client' ) {
            $options = ( object )$_POST;
            if ( !isset( $options->size ) ) $options->size = 500;
            if ( !isset( $options->page ) ) $options->page = 1;
            if ( !isset( $options->filter ) ) $options->filter = [];
            $list = $app->itemList( $form, ( array )$options );
            foreach ( $list['list'] as &$item ) {
                $item = wbTrigger( 'form', __FUNCTION__, 'beforeItemShow', [$form], $item );
            }
            $pages = ceil( $list['count'] / $options->size );
            $pagination = wbPagination( $options->page, $pages );
            echo json_encode( ['result'=>$list['list'], 'pages'=>$pages, 'page'=>$options->page, 'pagination'=>$pagination] );
            die;
        }
    }

}
