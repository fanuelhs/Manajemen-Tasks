<?php
class Mtask extends CI_Model {
    

    public function post($data) { 
        if ($this->db->insert('tasks', $data)) {
            return true;  
        } else {
            return false; 
        }
    }

    // public function get($id) { 
    //     return $this->db->get_where('tasks', ['task_id' => $id])->row_array();
    // }

    public function get($filters = [])
    {
        $this->db->select('*');
        $this->db->from('tasks');
    
        if (!empty($filters['title'])) {
            $this->db->like('title', $filters['title']);
        }
    
        if (!empty($filters['description'])) {
            $this->db->like('description', $filters['description']);
        }

        if (!empty($filters['status'])) {
            $this->db->like('status', $filters['status']);
        }
        
        if (!empty($filters['deadline_from']) && !empty($filters['deadline_to'])) {
            $this->db->where('deadline >=', $filters['deadline_from']);
            $this->db->where('deadline <=', $filters['deadline_to']);
        }

        if (!empty($filters['created_at'])) {
            $this->db->like('created_at', $filters['created_at']);
        }
    
        return $this->db->get()->result_array();
    }

    public function update($id, $data) {
        $this->db->where('task_id', $id);
        $this->db->update('tasks', $data);
    
        if ($this->db->affected_rows() > 0) {
            return true; 
        } else {
            return false; 
        }
    }
    public function delete($id) { 
        $this->db->trans_start(); 

        $this->db->where('task_id', $id);
        $this->db->delete('comments');

        $this->db->where('task_id', $id);
        $this->db->delete('tasks');

        $this->db->trans_complete();
        if ($this->db->delete('tasks', $id)) {
            return true;  
        } else {
            return false; 
        }
    }
}
