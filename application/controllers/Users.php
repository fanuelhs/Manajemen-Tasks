<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Muser');
    }
    public function create() { //Tambah users
        $data = [
            'username' => $this->input->post('username'), 
            'email' => $this->input->post('email'),
            'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
        ];

        if ($this->Muser->post($data)) { //Kondisi ketika user berhasil dibuat
            $user_id = $this->db->insert_id();
            $user = $this->Muser->get($user_id); 
            $this->output->set_status_header(201)->set_content_type('application/json')->set_output(json_encode([
                'message' => 'User Berhasil Dibuat',
                'user' => $user
            ]));
        } else { //Kondisi ketika user gagal dibuat
            $this->output->set_status_header(500)->set_output(json_encode(['message' => 'Maaf, Gagal Membuat User']));
        }
    }
    public function get($id) { // Dapat data menggunakan user_id
        $user = $this->Muser->get($id);
        if ($user) { // Kondisi ketika user ditemukan
            $this->output->set_content_type('application/json')->set_output(json_encode($user));
        } else { // Kondisi ketika user tidak ditemukan
            $this->output->set_status_header(404)->set_output(json_encode(['message' => 'User Tidak Ditemukan']));
        }
    }
}
