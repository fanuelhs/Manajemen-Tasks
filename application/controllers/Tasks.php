<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tasks extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mtask');
        $this->load->model('Mcomment');
    }

 
    public function create() { // Tambah task
        $data = [
            'user_id' => $this->input->post('user_id'),
            'title' => $this->input->post('title'),
            'description' => $this->input->post('description'),
            'status' => $this->input->post('status')
        ];

        if ($this->Mtask->post($data)) { //Kondisi ketika task berhasil dibuat
            $this->output->set_status_header(201)->set_content_type('application/json')->set_output(json_encode([
                'message' => 'Task Berhasil Dibuat',
                'task' => $data
            ]));
        } else { //Kondisi ketika task gagal dibuat
            $this->output->set_status_header(500)->set_output(json_encode(['message' => 'Maaf, Gagal Membuat Task']));
        }
    }

    public function get($id) { // Dapat data menggunakan task_id
        $task = $this->Mtask->get($id);
        if ($task) { // Kondisi ketika task ditemukan
            $this->output->set_content_type('application/json')->set_output(json_encode($task));
        } else { // Kondisi ketika task tidak ditemukan
            $this->output->set_status_header(404)->set_output(json_encode(['message' => 'Task Tidak Ditemukan']));
        }
    }

    public function update($id) {
    // Mendapatkan data dari form-data input
    $data = $this->input->post(); // CodeIgniter akan otomatis menangani form-data

    // Jika data kosong, kirim error
    if (empty($data)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Data tidak valid atau kosong. Pastikan data yang dikirim benar.'
        ]);
        return;
    }

    // Jika data valid, panggil metode update di model
    try {
        // Tambahkan logging untuk debug
        log_message('debug', 'Data received for update: ' . print_r($data, true));
        // Pastikan Anda mengirimkan ID dan data sebagai argumen
        $this->Mtask->update_task($id, $data);
        echo json_encode(['status' => 'success', 'message' => 'Task Berhasil Diperbarui']);
    } catch (Exception $e) {
        // Tambahkan logging untuk menangkap error
        log_message('error', 'Update failed: ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}


    public function delete($id) { // Menghapus data task menggunakan task_id
        if ($this->Mtask->delete($id)) { // Kondisi ketika task dihapus
            $this->output->set_content_type('application/json')->set_output(json_encode(['message' => 'Gagal Menghapus Task']));
        } else { //Kondisi ketika task gagal dihapus
            $this->output->set_status_header(500)->set_output(json_encode(['message' => 'Task Berhasil Dihapus ']));
        }
    }
        public function create_comment($task_id) { // Tambah comment
        $data = [
            'task_id' => $task_id,
            'user_id' => $this->input->post('user_id'),
            'comment' => $this->input->post('comment')
        ];

        if ($this->Mcomment->post($data)) { // Kondisi ketika comment berhasil dibuat
            $this->output->set_status_header(201)->set_content_type('application/json')->set_output(json_encode([
                'message' => 'Comment Berhasil Ditambahkan',
                'comment' => $data
            ]));
        } else { // Kondisi ketika comment gagal dibuat
            $this->output->set_status_header(500)->set_output(json_encode(['message' => 'Gagal Menambahkan Comment']));
        }
    }

    public function get_comments($task_id) {
        $comments = $this->Mcomment->get($task_id);
        echo json_encode($comments);
    }
    
}
