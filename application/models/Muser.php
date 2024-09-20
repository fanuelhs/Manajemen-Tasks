<?php
class Muser extends CI_Model {

    public function post($data) { //Membuat data user
        return $this->db->insert('users', $data);
    }

    public function get($id) { //Mendapatkan data user menggunakan id_user
        return $this->db->get_where('users', ['user_id' => $id])->row_array();
    }
}

