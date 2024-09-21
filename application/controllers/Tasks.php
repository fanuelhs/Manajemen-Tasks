<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tasks extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Mtask'); 
        $this->load->model('Mcomment'); 
        $this->load->library('form_validation'); 
    }

 
    public function create() { // Tambah task
        $data = [ 
            'user_id' => $this->input->post('user_id'),
            'title' => $this->input->post('title'),
            'description' => $this->input->post('description'),
            'status' => $this->input->post('status')
        ];
        $valid_status = ['done', 'on progress', 'pending'];

        if (!in_array($data['status'], $valid_status)) { //  Kondisi ketika pengisian input status tidak sesuai
            echo json_encode([
                'Status' => 'Error', 
                'Message' => 'Status harus salah satu dari: done, on progress, atau pending']);
            return;
        }

        $insert = $this->Mtask->post($data);
        if ($insert) { // Kondisi ketika task berhasil dibuat
        echo json_encode([
        'Status' => 'Success',
        'Message'=> 'Task Berhasil Dibuat',
        'Data' => $data]);
    } else { // Kondisi ketika task gagal dibuat
        echo json_encode([
            'Status' => 'Error', 
            'Message' => 'Task Gagal Dibuat']);
    }
    }

    public function get($id) { // Dapat data menggunakan task_id
        $task = $this->Mtask->get($id);
        if ($task) { // Kondisi ketika task ditemukan
            $this->output->set_output(json_encode($task));
        } else { // Kondisi ketika task tidak ditemukan
            $this->output->set_output(json_encode(['Message' => 'Task Tidak Ditemukan']));
        }
    }

    public function update($id) {
        $data = array(
            'user_id' => $this->input->input_stream('user_id'),
            'title' => $this->input->input_stream('title'),
            'description' => $this->input->input_stream('description'),
            'status' => $this->input->input_stream('status')
        );
        
        $valid_status = ['done', 'on progress', 'pending'];

        if (!in_array($data['status'], $valid_status)) { // Kondisi ketika pengisian input status tidak sesuai
            echo json_encode([
                'Status' => 'Error', 
                'Message' => 'Status harus salah satu dari: done, on progress, atau pending']);
            return;
        }
        
        if ($this->Mtask->update($id, $data)) { // Kondisi ketika berhasil meperbarui
            echo json_encode([
                'Status' => 'success',
                'Message' => 'Task Berhasil Diperbarui.',
                'Data' => $data
            ]);
        } else {
            echo json_encode([ // Kondisi ketika gagal meperbarui
                'Status' => 'error',
                'Message' => 'Gagal Untuk Memperbarui'
            ]);
        }
    }


    public function delete($id) { // Menghapus data task menggunakan task_id
        if ($this->Mtask->delete($id)) { // Kondisi ketika task dihapus
            $this->output->set_output(json_encode(['Message' => 'Task Berhasil Dihapus']));
        } else { //Kondisi ketika task gagal dihapus
            $this->output->set_output(json_encode(['Message' => 'Gagal Menghapus Task']));
        }
    }
        public function create_comment($task_id) { // Tambah comment
        $data = [
            'task_id' => $task_id,
            'user_id' => $this->input->post('user_id'),
            'comment' => $this->input->post('comment')
        ];

        if ($this->Mcomment->post($data)) { // Kondisi ketika comment berhasil dibuat
            $this->output->set_output(json_encode([
                'Message' => 'Comment Berhasil Ditambahkan',
                'Data' => $data
            ]));
        } else { // Kondisi ketika comment gagal dibuat
            $this->output->set_output(json_encode(['Message' => 'Gagal Menambahkan Comment']));
        }
    }

    public function get_comment($task_id) { // Mengambil semua comment dari task_id
        $comments = $this->Mcomment->get($task_id);
        echo json_encode($comments);
    }
    
}
