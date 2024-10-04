<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tasks extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Mtask');
        $this->load->model('Mcomment');
        $this->load->model('Muser');
        $this->load->library('form_validation');
    }
    
    public function create()
    { 
        // Tambah task
        $this->form_validation->set_rules('user_id', 'User ID', 'required|integer', [ // From validation
            'required' => 'User ID Harus Diisi',
            'integer' => 'User ID Harus Diisi Dengan Angka'
        ]);
        $this->form_validation->set_rules('title', 'Title', 'required', [
            'required' => 'Title Harus Diisi'
        ]);
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[done,on progress,pending]', [
            'required' => 'Status Harus Diisi',
            'in_list' => 'Status Harus Salah Satu Dari: done, on progress, atau pending'
        ]);

        if ($this->form_validation->run() == FALSE) { // Kondisi ketika validasi gagal
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Validasi Gagal',
                    'Errors' => $this->form_validation->error_array()
                ]));
        } else { // Kondisi ketika validasi berhasil
            $data = [
                'user_id' => $this->input->post('user_id'),
                'title' => $this->input->post('title'),
                'description' => $this->input->post('description'),
                'status' => $this->input->post('status')
            ];

            $user_id = $this->input->post('user_id');
            $user = $this->Muser->get($user_id);

            if (!$user) { // Kondisi ketika user_id tidak ditemukan
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode([
                        'Status' => 'Error',
                        'Message' => 'User ID Tidak Ditemukan'
                    ]));
                return;
            }

            $insert = $this->Mtask->post($data);
            if ($insert) { // Kondisi ketika task berhasil dibuat
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode([
                        'Status' => 'Success',
                        'Message' => 'Task Berhasil Dibuat',
                        'Data' => $data
                    ]));
            } else { // Kondisi ketika task gagal dibuat
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode([
                        'Status' => 'Error',
                        'Message' => 'Task Gagal Dibuat'
                    ]));
            }
        }
    }


    public function get($id)
    { 
        // Dapat data menggunakan task_id
        $task = $this->Mtask->get($id);
        if ($task) { // Kondisi ketika task ditemukan
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Task Berhasil Ditemukan',
                'Data' => $task
            ]));
        } else { // Kondisi ketika task tidak ditemukan
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Task Tidak Ditemukan'
            ]));
        }
    }


    public function update($id)
    {
        $task = $this->Mtask->get($id);

        if (!$task) { // Kondisi ketika task tidak ditemukan
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Task Tidak Ditemukan'
                ]));
            return;
        }
        $data = array(
            'user_id' => $this->input->post('user_id'),
            'title' => $this->input->post('title'),
            'description' => $this->input->post('description'),
            'status' => $this->input->post('status')
        );
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('user_id', 'User ID', 'required|integer', [ // From validation
            'required' => 'User ID Harus Diisi',
            'integer' => 'User ID Harus Diisi Dengan Angka'
        ]);
        $this->form_validation->set_rules('title', 'Title', 'required', [
            'required' => 'Title Harus Diisi'
        ]);
        $this->form_validation->set_rules('status', 'Status', 'required|in_list[done,on progress,pending]', [
            'required' => 'Status Harus Diisi',
            'in_list' => 'Status Harus Salah Satu Dari: done, on progress, atau pending'
        ]);

        if ($this->form_validation->run() == FALSE) { // Kondisi ketika validasi gagal
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Validasi Gagal',
                    'Errors' => $this->form_validation->error_array()
                ]));
            return;
        }
        
        if ($this->Mtask->update($id, $data)) { // Kondisi ketika berhasil meperbarui
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Success',
                    'Message' => 'Task Berhasil Diperbarui.',
                    'Data' => $data
                ]));
        } else {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([ // Kondisi ketika gagal meperbarui
                    'Status' => 'Error',
                    'Message' => 'Gagal Untuk Memperbarui'
                ]));
        }
    }


    public function delete($id)
    { 
        // Menghapus data task menggunakan task_id
        $task = $this->Mtask->get($id);

        if (!$task) { // Kondisi ketika task tidak ditemukan
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Task Tidak Ditemukan'
                ]));
            return;
        }

        if ($this->Mtask->delete($id)) { // Kondisi ketika task dihapus
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Task Berhasil Dihapus']));
        } else { //Kondisi ketika task gagal dihapus
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Error',
                'Message' => 'Gagal Menghapus Task']));
        }
    }
    public function create_comment($task_id)
    { 
        // Tambah comment
        $task = $this->Mtask->get($task_id);
        if (!$task) { // Kondisi ketika task tidak ditemukan
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Task Tidak Ditemukan'
                ]));
            return;
        }
        $this->form_validation->set_rules('comment', 'Comment', 'required|max_length[100]', [ // From validation
            'required' => 'Comment Harus Diisi',
            'max_length' => 'Comment Tidak Boleh Lebih Dari 100 Karakter'
        ]);

        if ($this->form_validation->run() == FALSE) { // Kondisi ketika validasi gagal
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Validasi Gagal',
                    'Errors' => $this->form_validation->error_array()
                ]));
            return;
        }
        $user_id = $task['user_id'];
        $data = [
            'task_id' => $task_id,
            'user_id' => $user_id,
            'comment' => $this->input->post('comment')
        ];

        if ($this->Mcomment->post($data)) { // Kondisi ketika comment berhasil dibuat
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Comment Berhasil Ditambahkan',
                'Data' => $data
            ]));
        } else { // Kondisi ketika comment gagal dibuat
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Error',
                'Message' => 'Gagal Menambahkan Comment']));
        }
    }

    public function get_comment($task_id)
    { 
        // Dapat comment menggunakan task_id
        $task = $this->Mtask->get($task_id);
        if (!$task) { // Kondisi ketika task tidak ditemukan
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Task Tidak Ditemukan'
                ]));
            return;
        }
        $comments = $this->Mcomment->get($task_id);
       if ($comments) { // Kondisi ketika task ditemukan
        $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Comment Berhasil Ditemukan',
                'Data' => $comments
            ]));
    } else { // Kondisi ketika task tidak ditemukan
        $this->output->set_content_type('application/json')
            ->set_output(json_encode([
                'Status' => 'Error',
                'Message' => 'Comments Tidak Ditemukan']));
    }
    }
}
