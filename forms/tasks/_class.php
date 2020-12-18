<?php
class tasksClass extends cmsFormsClass {

    function beforeItemShow($item) {
        if (isset($item['comments'])) {
            foreach((array)$item['comments'] as $key => $com) {
                if (isset($com['time'])) {
                    $com['time'] = date('d.m.Y H:i', strtotime($com['time']));
                    $item['comments'][$key] = $com;
                }
            }
        }
        $item['time'] = date('d.m.Y H:i', strtotime($item['_created']));
        return $item;
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
