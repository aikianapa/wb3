<?php
class notesClass extends cmsFormsClass {

    function afterItemRead(&$item)
    {
        $item['time'] = date('d.m.Y H:i', strtotime($item['_created']));
        $item['short'] = wbGetWords($item['note'], 20);
    }

    function afterItemSave(&$item) {
        return $this->afterItemRead($item);
    }


    function beforeItemShow(&$item) {
        if (isset($item['_created'])) {
            $item['time'] = date('d.m.Y H:i', strtotime($item['_created']));
            $item['short'] = wbGetWords($item['note'], 20);
        }
    }

    function addComment() {
        $id = $this->app->vars('_post.id');
        $task = $this->app->itemRead('tasks', $id);
        if (!$task OR $this->app->vars('_post.comment') == '') {
            echo json_encode(false);
        } else {
            !isset($task['comments']) ? $task['comments'] = [] : null;
            $comment = [
                'comment' => $this->app->vars('_post.comment'),
                'time' => date('d-m-Y H:i:s')
            ];
            $task['comments'][] = $comment;
            $res = $this->app->itemSave('tasks', $task);
            if ($res) {
                echo json_encode($comment);
            }
        }
    }

    function removeComments() {
        $id = $this->app->vars('_post.id');
        $idx = $this->app->vars('_post.idx');
        $task = $this->app->itemRead('tasks', $id);
        if (!$task OR !isset($task['comments']) OR !isset($task['comments'][$idx])) {
            echo json_encode(false);
        } else {
            array_splice($task['comments'], $idx, 1);
            $res = $this->app->itemSave('tasks', $task);
            if ($res) {
                echo json_encode(true);
            } else {
                echo json_encode(false);
            }
        }
    }

    function listComments()
    {
        $id = $this->app->vars('_post.id');
        $task = $this->app->itemRead('tasks', $id);
        $task = $this->beforeItemShow($task);
        if (!$task) {
            echo json_encode(false);
        } else {
            !isset($task['comments']) ? $task['comments'] = [] : null;
            echo json_encode($task['comments']);
        }
    }


}
?>
