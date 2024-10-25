<?php
class Mcomment extends CI_Model {

    public function post($data) { 
        return $this->db->insert('comments', $data);
    }

    public function get($task_id) {
        return $this->db->get_where('comments', ['task_id' => $task_id])->result_array();
    }

    public function getFilter($filters = [])
    {
        $this->db->select('comments.*, users.username, CONCAT(users.firstname, " ", users.lastname) as name');
        $this->db->from('comments');
        $this->db->join('users', 'comments.user_id = users.user_id', 'left');  
        
        if (!empty($filters['task_id'])) {
            $this->db->where('comments.task_id', $filters['task_id']);
        }
        
        if (!empty($filters['user_id'])) {
            $this->db->like('comments.user_id', $filters['user_id']);
        }

        if (!empty($filters['username'])) {
            $this->db->like('users.username', $filters['username']);
        }
    
        if (!empty($filters['name'])) {
            $this->db->like('CONCAT(users.firstname, " ", users.lastname)', $filters['name']);
        }
    
        if (!empty($filters['comment'])) {
            $this->db->like('comments.comment', $filters['comment']);
        }

        return $this->db->get()->result_array();
    }
    
}

