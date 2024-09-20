<?php
class Mtask extends CI_Model {
    

    public function post($data) { //Membuat data task
        return $this->db->insert('tasks', $data);
    }

    public function get($id) { //Mendapatkan data task menggunakan task_id
        return $this->db->get_where('tasks', ['task_id' => $id])->row_array();
    }

    public function update_task($id, $data) {
        // Pastikan data di array $data memiliki key yang sesuai dengan kolom di database
        $this->db->where('task_id', $id);
        $this->db->update('tasks', $data); // pastikan $data adalah array berisi title, description, status, dll.
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
