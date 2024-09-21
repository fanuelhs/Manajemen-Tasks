<?php
class Mtask extends CI_Model {
    

    public function post($data) { 
        if ($this->db->insert('tasks', $data)) {
            return true;  
        } else {
            return false; 
        }
    }

    public function get($id) { 
        return $this->db->get_where('tasks', ['task_id' => $id])->row_array();
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
