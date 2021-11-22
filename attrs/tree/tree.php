<?php

use Adbar\Dot;

class attrTree {
    public function __construct( $dom ) {
        $this->tree( $dom );
    }

    public function tree( $dom ) {
        tagTree( $dom, $dom->item );
    }
}

function tagTree( &$dom, $Item = null ) {
    $dom->removeAttr( 'wb-tree' );
    $save = $dom->params;
    if ( $Item == null ) $Item = $dom->item;
    if ( !( ( array )$Item === $Item ) ) $Item = array( $Item );
    if ( isset( $dom->params->tree ) ) $dom->params = ( object )$dom->params->tree;

    isset( $dom->params->table ) ? $table = $dom->params->table : $table = null;
    isset( $dom->params->name ) ? $name = $dom->params->name : $name = $dom->attr( 'name' );
    isset( $dom->params->form ) ? $form = $dom->params->form : $form = null;
    isset( $dom->params->from ) ? $from = $dom->params->from : $from = null;
    isset( $dom->params->item ) ? $item = $dom->params->item : $item = null;
    isset( $dom->params->type ) ? $type = $dom->params->type : $type = null;
    isset( $dom->params->field ) ? $field = $dom->params->field : $field = null;

    if ( $table && !$form ) $form = $dom->params->form = $table;
    
    if ( $form > '' AND $item > '' ) {
        $Item = $dom->app->itemRead( $form, $item );
        if (!$field) $field = 'tree';
    }

    $srcData = $Item;

    !isset( $dom->params->from ) AND !isset( $dom->params->field ) ? $field = $name : null;
    $dom->is( 'select' ) AND !isset( $dom->params->form ) AND !$field ?  $field = 'tree' : null;
    
    if ( $form == '' AND $item > '' ) {
        $Item = $dom->app->treeRead( $item );
        $field = 'tree';
    }
    if ($dom->params('from')) {
        if ($dom->app->vars($dom->params->from) > '') {
            $Item = $dom->app->vars($dom->params->from);
        } else {
            $Item = $dom->getField($dom->params->from);
        }
    }


    if ( $field > '' ) {
        if ( !isset( $dom->params->name ) ) $dom->params->name = $field;
        if ( strpos( $field, '.' ) ) {
            $fields = new Dot();
            $fields->setReference( $Item );
            $fld = explode( '.', $field );
            if ( isset( $Item[$fld[0]]['dict'] ) AND isset( $Item[$fld[0]]['data'] ) ) {
                // значит дерево
                $field = $fld[0].'.data.';
                array_shift( $fld );
                $branch = $field.implode( '.children.', $fld );

            } else {
                $branch = $field;
            }
            $Item = [$fields->get( $branch )];
        } else if ( !isset( $Item[$field] ) ) {
            $id = $dom->app->newId();
            $Item = [$id=>['id'=>$id, 'name'=>'']];
            $tree['dict'] = [];
        } else {
            isset( $Item[$field]['dict'] ) ? $tree['dict'] = $Item[$field]['dict'] : $tree['dict'] = [];
            isset( $Item[$field]['data'] ) ? $Item = $Item[$field]['data'] : null;
            if (isset($Item['data'])) unset( $Item['data'] );
            if (isset($Item['dict'])) unset( $Item['dict'] );
        }
    }

    if ( $dom->params('dict') ) {
        $dictdata = wbItemRead( 'catalogs', $dom->params->dict );
        if ($dictdata && isset($dictdata['tree'])) {
            $dictdata = $tree = $dictdata['tree'];
            $Item = $dictdata['data'];
        }
        unset( $dictdata );
    }

    if ( ( $dom->hasAttr( 'name' ) OR $dom->is( 'input' ) ) AND !$dom->is( 'select' ) ) {
        $inp = tagTreeInput( $dom, ['data'=>$Item, 'dict'=>$tree['dict']] );
        $dom->replaceWith( $inp );

    } elseif ( $dom->is( 'select' ) ) {
        $select = new tagTreeSelect();
        $select->dom = &$dom;
        $select->tree = $Item;
        $select->dom->params = $save;
        isset($save->strict) ? $select->strict = $save->strict : $select->strict = true;
        $select->stage();
        $dom->params('multiple') == 'on' ? $dom->attr('wb-select2', true) : null;
        //tagTreeUl( $dom, $Item, null, $srcVal );
    } else {
        tagTreeUl( $dom, $Item, null, $srcData );
    }
    $dom->params = $save;
}

class tagTreeSelect {
    function stage() {
        $dom = &$this->dom;
        $app = &$this->dom->app;
        $params = &$dom->params;
        
        if ( $this->dom->is( 'select' ) ) {
            $this->tpl = $this->dom->inner();
            $this->opt = $this->dom->find('option',0)->clone();
            $this->dom->html( '' );
            $this->lvl = 0;
            $this->idx = 0;
            $this->limit = -1;
            $this->level = -1;
            $this->placeholder = $this->dom->attr('placeholder');
            !isset( $params->parent ) ? $params->parent = 'true' : null;
            !isset($params->children) ? $params->children = 'true' : null;
            !isset($params->level) ? $params->level = 0 : null;

            $params->parent == 'true' ? $this->parent = true : $this->parent = false;
            $params->children == 'true' ? $this->children = true : $this->children = false;
            $params->level ? $this->level = intval( $params->level ) : null;

            $this->select = &$this->dom;

            if ( $dom->params("branch") > '' ) {
                if ($this->strict == 'false') {
                    $params->branch = preg_replace('/\%(.*)\%/', "", $params->branch);
                    $this->tree = wbTreeFindBranch($this->tree, $params->branch);
                    if (substr($params->branch,-2) == '->') {
                        $tmp = [];

                        foreach($this->tree[0]['children'] as $branch ) {
                            if (isset($branch['children'])) $tmp = array_merge($tmp,$branch['children']);
                        }
                        $this->tree[0]['children'] = $tmp;
                    }
                } else {
                    $this->tree = wbTreeFindBranch($this->tree, $params->branch);
                }
                
            }

            if (isset($params->sort) && isset($this->tree[0]['children'])) {
                $this->tree[0]['children'] = wbArraySort($this->tree[0]['children'], $params->sort);
            }
        }
        $flag = false;
        if ( !isset( $params->rand ) ) $params->rand = false;
        if ( ( array )$this->tree === $this->tree ) {
            if ( $params->rand == true ) shuffle( $tree );
            foreach ( $this->tree as $i => $item ) {
                if ( !( ( array )$item === $item ) ) $item = ( array )$item;
                if ( !isset( $item['id'] ) ) $item['id'] = $i;
                $line = $app->fromString( $this->tpl );

                if ( $this->parent === 'disabled' ) {
                    $line->attr( 'disabled', true );
                    $this->parent = null;
                }

                $item['_parent'] = &$Item;
                if ( $this->children == false && $this->lvl > 1 ) return;

                $line->fetch( $item );
                if ( $line->tag() == 'option' ) {
                    $line->prepend( '<span>'.str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;', $this->lvl ).'</span>' );
                }

                $this->lvl++;

                $item['_idx'] = $this->idx;
                $item['_ndx'] = $this->idx+1;
                $item['_lvl'] = $this->lvl;

                if ( !isset( $item['active'] ) ) $item['active'] = '';
                if ( $this->parent && $item['active'] == 'on' )  $flag = true;
                if ( $this->level > 0 && $this->level !== $this->lvl ) $flag = false;
                if ( $app->vars( '_route.controller' ) == 'ajax' ) {
                    // применяем фильтр только для ajax вызовов
                    if ( $app->vars( '_post._filter' ) && $flag ) $flag = $app->filterItem( $item );
                }

                if ( $flag == true ) $this->select->append( $line );

                if ( isset( $item['children'] ) AND ( array )$item['children'] === $item['children'] AND count( $item['children'] ) ) {

                    if ( $this->lvl > 0 ) $this->parent = true;
                    $option = new tagTreeSelect();
                    $option->dom = $line;
                    $option->lvl = $this->lvl;
                    $option->idx = $item['_idx'];
                    $option->tree = $item['children'];
                    $option->tpl = $this->tpl;
                    $option->strict = &$this->strict;
                    $option->parent = $this->parent;
                    $option->children =  $this->children;
                    $option->level = $this->level;
                    $option->select = &$this->select;

                    if ( $item['active'] == 'on' ) $option->stage();

                }
                $this->idx++;
                $this->lvl--;
            }
        }
            if ($this->dom->is('select[placeholder]')) {
                if ($this->opt) {
                    $this->opt->attr('value', '');
                    $this->opt->inner($this->placeholder);
                    $this->opt->setAttributes([]);
                    $this->dom->prepend($this->opt->outer());
                } else {
                    $this->dom->prepend('<option value="">'.$this->dom->attr('placeholder').'</option>');
                }
            }
    }

}

function tagTreeInput( $dom, $data = array() ) {
    $tpl = $dom->app->fromFile( __DIR__ .'/tree_ui.php' );
    $tpl->params = $dom->params;
    tagTreeUl( $tpl, $data['data'] );
    $data = wbJsonEncode( $data );
    $tpl->append( "<textarea type='json' class='wb-tree-data wb-value' name='{$dom->params->name}'>{$data}</textarea>
    <script type='wbapp'>
        wbapp.loadScripts(['/engine/attrs/tree/tree.js','/engine/js/jquery-ui.min.js'],'wb-tree-js');
    </script>");
    return $tpl;
}

function tagTreeUl( &$dom, $Item = array(), $param = null, $srcVal = array() ) {
    $limit = -1;
    $lvl = 0;
    $level = -1;
    $idx = 0;
    $tree = $Item;
    $parent_id = '';
    $pardis = 0;

    $dom->params('branch') > '' ? $branch = $dom->params('branch') : $branch = null;
    $dom->params('limit') > '' ? $limit = intval( $dom->params('limit') ) : $limit = -1;
    $dom->params('parent') == 'false' ? $parent = 0 : $parent = 1;
    $dom->params('children') == 'false' ? $children = 0 : $children = 1;
    $dom->params('rand') == 'true' ? $rand = true : $rand = false;
    $srcItem = $Item;
    $tag = $dom->tagName;
    if ( $param == null ) {
        $dom->params('name') > '' ? $name  = $dom->params('name') : $name = $dom->attr( 'name' );
        $dom->params('from') > '' ? $name = $dom->params('from') : null;
        $tpl = $dom->inner();
        $tree = &$Item;
        if ( $dom->params('call') > '') {
            $call = $dom->params('call');
            $tree = @$call();
        }
    } else {
        foreach ( $param as $k =>$val ) $$k = $val;
    }
    if ( !isset( $level ) ) $level = '';
    $dom->html( '' );
    if ( $branch ) {
        if ( $tree == NULL AND $branch > '' ) {
            $tree = wbTreeFindBranch( $Item['children'], $branch );
        } else {
            $tree = wbTreeFindBranch( $tree, $branch );
        }
    }
    $idx = 0;

    if ( ( array )$tree === $tree ) {
        if ( $rand == true ) shuffle( $tree );
        foreach ( $tree as $i => $item ) {
            if ( !( ( array )$item === $item ) ) $item = ( array )$item;
            $item['_parent'] = &$tree;
            $lvl++;
//            $item = ( array )$srcVal + ( array )$item;
            if ( !isset( $item['id'] ) ) $item['id'] = $i;
            $item['_pid'] = $parent_id;
            $item['_idx'] = $idx;
            $item['_ndx'] = $idx+1;
            $item['_lvl'] = $lvl-1;
            $item['_val'] = $item;

            if ( $parent_id>'' ) $item['%id'] = $parent_id;
            $line = $dom->app->fromString( '<level>'.$tpl.'</level>' );
            $child = $dom->app->fromString( $tpl );

            $line->fetch( $item );
            if ( $parent == 0 OR ( isset( $item['children'] ) AND ( array )$item['children'] === $item['children'] AND count( $item['children'] ) ) ) {
                if ( $pardis == 1 AND ( $limit !== $lvl-1 ) ) $line->attr( 'disabled', true );
                if ( $lvl>1 ) $parent = 1;
                if ( isset( $item['children'] ) ) tagTreeUl( $child, $item['children'], array( 'name'=>$name, 'tag'=>$tag, 'lvl'=>$lvl, 'tpl'=>$tpl, 'idx'=>$idx, 'level'=>$level, 'parent_id'=>$item['id'], 'pardis'=>$pardis, 'parent'=>$parent, 'children'=>$children, 'limit'=>$limit ), $srcVal );
                if ( ( $limit == -1 OR $lvl <= $limit ) ) {
                    if ( $parent !== 1 ) {
                        $lvl--;
                        $line->inner( $child->inner() );
                    } else {
                        if ( $children == 1 AND isset( $item['children'] ) ) {
                            $line->children( 'level' )->children()->append( "<{$tag}>".$child->inner()."</{$tag}>" );

                        }
                    }
                }
            }
            $idx++;
            $lvl--;
            if ( isset( $line ) ) {
                if ( $line->tag() == 'level' ) {
                    $dom->append( $line->inner() );
                } else {
                    $dom->append( $line->outer() );

                }

            }
        }
        $dom->find('wb')->unwrap('wb');
    }
}

function tagTreeForm( $dict = [], $data = [] ) {
    $app = new wbApp();
    $fldset = $app->fromFile( __DIR__ . '/tree_fldset.php' );
    $out = '';
    if ( ( array )$dict === $dict ) {
        foreach ( $dict as $fld ) {
            $set = $fldset->clone();
            $set->fetch( $fld )->clearValues();
            $set->find( 'label' )->inner( $fld['label'] );
            $set->find( 'div.col-12' )->append( $app->fieldBuild( $fld, $data['data'] ) );
            $out .= $set->outer().'\n';
            //$out .= wbFieldBuild( $fld, $data ).'\n';
        }
    }
    return $out;
}

function tagTreeProp( $type = null ) {
    $app = new wbApp();
    $out = $app->fromFile( __DIR__ . '/tree_prop.php' );
    if ( $type == null ) {
        $type = $_POST['type'];
        $com = $app->fromString( $out->find( '[type=common]' )->html(), true );
        $com->fetch( $_POST['dict'] );

    }
    if ( $out->find( "[type={$type}]" )->length ) {
        $out = $app->fromString( $out->find( "[type={$type}]" )->html(), true );
        $out->fetch( $_POST['dict'] );
        if ( isset( $com ) ) $out->find( 'form' )->append( $com->find( 'form' )->html() );
    } else {
        $out = $com;
    }
    $out->fetch( $_POST['dict'] );
    return wb_json_encode( ['content'=>$out->html()] );
}

function ajax__tree_getform() {
    // build form to edit branch
    $app = new wbApp();
    if ( $app->vars->get( '_route.params.0' ) == 'prop' ) return tagTreeProp();
    if ( $app->vars->get( '_route.params.0' ) == 'lang' ) return tagTreeProp( 'lang' );

    if ( $app->vars->get( '_route.params.0' ) == 'dict' ) {
        $dict = $app->fromFile( __DIR__ . '/tree_dict.php' );
        $dict->fetch( $_POST );
        return wb_json_encode( ['content'=>$dict->outer(), 'post'=>$_POST] );
    }

    $data = tagTreeForm( $_POST['dict'], $_POST['data'] );
    $data = $app->fromString( $data );
    $data->fetch( $_POST['data'] );
    if ( $app->vars->get( '_route.params.0' ) == 'data' ) return wb_json_encode( ['content'=>$data->outer(), 'post'=>$_POST] );
    $out = $app->fromFile( __DIR__ . '/tree_edit.php' );
    $out->fetch( $_POST['data'] );
    $out->find( '.treeData > form' )->html( $data );
    return wb_json_encode( ['content'=>$out->outer(), 'post'=>$_POST] );
}

function ajax__tree_update() {
    $app = new wbApp();
    $tpl = $app->fromFile( __DIR__ .'/tree_ui.php', false );
    $tpl->fetch( $_POST );
    return wb_json_encode( ['content'=>$tpl->html()] );
}
?>
