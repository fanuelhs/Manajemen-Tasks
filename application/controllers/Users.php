<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Muser');
    }
    public function create() {
        $data = [
            'username' => $this->input->post('username'), 
            'email' => $this->input->post('email'),
            'password' =>$this->input->post('password'),
        ];
        if (strpos($data['email'], '@gmail.com') === false) { // Kondisi ketika tidak ada @gmail.com
            echo json_encode([
                'Status' => 'Error', 
                'Message' => 'Email harus menggunakan domain @gmail.com']);
            return;
        }
        
        $insert = $this->Muser->post($data);
        if ($insert) { // Kondisi ketika user berhasil dibuat
        echo json_encode([ 
        'Status' => 'Success', 
        'Message'=> 'User Berhasil Dibuat',
        'Data' => $data]);
        } else { // Kondisi ketika user gagal dibuat
            echo json_encode([
            'Status' => 'Error', 
            'Message' => 'User Gagal Dibuat']);
    }
}
    public function get($id) { // Dapat data menggunakan user_id
        $user = $this->Muser->get($id);
        if ($user) { // Kondisi ketika user ditemukan
            $this->output->set_output(json_encode($user));
        } else { // Kondisi ketika user tidak ditemukan
            $this->output->set_output(json_encode(['Message' => 'User Tidak Ditemukan']));
        }
    }
}
