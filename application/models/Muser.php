<?php
class Muser extends CI_Model {

    public function post($data) {
        if ($this->db->insert('users', $data)) {
            return true;  
        } else {
            return false; 
        }
    }

    public function get($id) { 
        return $this->db->get_where('users', ['user_id' => $id])->row_array();
    }
}

