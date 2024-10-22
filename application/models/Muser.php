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
    public function getFilter($filter = [])
{
    $this->db->select('*');
    $this->db->from('users');
    
    if (!empty($filter['name'])) {
        $this->db->like('CONCAT(firstname, " ", lastname)', $filter['name']);
    }
    
    if (!empty($filter['username'])) {
        $this->db->like('username', $filter['username']);
    }
    
    if (!empty($filter['email'])) {
        $this->db->like('email', $filter['email']);
    }

    return $this->db->get()->result_array();
}
    public function login($email = null) {
        $this->db->where('email', $email);

        $query = $this->db->get('users');
        return $query->row_array();
    }
}

