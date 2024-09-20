<?php
class Mtask extends CI_Model {
    

    public function post($data) { //Membuat data task
        return $this->db->insert('tasks', $data);
    }

    public function get($id) { //Mendapatkan data task menggunakan task_id
        return $this->db->get_where('tasks', ['task_id' => $id])->row_array();
    }

    public function update_task($id, $data) {
        $this->db->where('task_id', $id);
        $this->db->update('tasks', $data);
    
        if ($this->db->affected_rows() > 0) {
            return true; // Update berhasil
        } else {
            log_message('error', $this->db->last_query()); // Log query untuk debugging
            return false; // Update gagal
        }
    }
    public function delete($id) { //Menghapus data task menggunakan task_id
        $this->db->trans_start(); // Membuat transaksi agar berjalan bersamaan 

        // Menghapus comment menggunakan task_id
        $this->db->where('task_id', $id);
        $this->db->delete('comments');

        // Menghapus comment menggunakan task_id
        $this->db->where('task_id', $id);
        $this->db->delete('tasks');

        $this->db->trans_complete();
    }
}
