<?php
class Mcomment extends CI_Model {

    public function post($data) { //Menambahkan comment pada task
        return $this->db->insert('comments', $data);
    }

    public function get($task_id) {
        return $this->db->get_where('comments', ['task_id' => $task_id])->result_array();
    }
}

