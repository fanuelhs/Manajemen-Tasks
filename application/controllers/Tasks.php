<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tasks extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Mtask'); 
        $this->load->model('Mcomment'); 
        $this->load->library('form_validation'); 
    }

 
    public function create($data) { // Tambah task
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
        $data = array(
            'user_id' => $this->input->input_stream('user_id'),
            'title' => $this->input->input_stream('title'),
            'description' => $this->input->input_stream('description'),
            'status' => $this->input->input_stream('status')
        );
        
        // Debug untuk memastikan data diterima
        echo json_encode($data); 
        
        // Lanjutkan jika data lengkap
        if (empty($data['user_id']) || empty($data['title']) || empty($data['description']) || empty($data['status'])) {
            echo "Data tidak lengkap. Pastikan semua field diisi.";
            return;
        }
    
        // Coba lakukan update
        if ($this->Mtask->update_task($id, $data)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Task Berhasil Diperbarui.',
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal Untuk Memperbarui. Periksa log untuk detail lebih lanjut.'
            ]);
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
