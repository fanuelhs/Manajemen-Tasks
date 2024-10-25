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
        $this->form_validation->set_rules('deadline', 'Deadline', 'required|callback_validate_date', [
            'required' => 'Deadline Harus Diisi',
            'validate_date' => 'Format Tanggal Tidak Sesuai, gunakan format YYYY-MM-DD / YY-M-D'
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
                'status' => $this->input->post('status'),
                'deadline' => $this->input->post('deadline')
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
                        'Data' => [
                            'user_id' => $data['user_id'],
                            'title' => $data['title'],
                            'description' => $data['description'],
                            'status_task' => $data['status'],
                            'deadline' => $data['deadline']
                        ]
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

    public function validate_date($date)
    {
        // Kondisi format tanggal 
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || preg_match('/^\d{2}-\d{1,2}-\d{1,2}$/', $date)) {
            // Tambahkan validasi tanggal yang sebenarnya
            $date_parts = explode('-', $date);
            if (count($date_parts) == 3) {
                $year = (int) $date_parts[0];
                $month = (int) $date_parts[1];
                $day = (int) $date_parts[2];

                // Cek apakah bulan valid
                if ($month < 1 || $month > 12) {
                    return false;
                }

                // Cek jumlah hari sesuai bulan
                if ($month == 2) {
                    // Cek tahun kabisat
                    $isLeapYear = (($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0));
                    $max_days = $isLeapYear ? 29 : 28;
                } elseif (in_array($month, [4, 6, 9, 11])) {
                    $max_days = 30;
                } else {
                    $max_days = 31;
                }

                if ($day < 1 || $day > $max_days) {
                    return false;
                }
                return true; // Mengembalikan jika Format dan tanggal valid
            }
        }
        return false; // Mengembalikan jika Format tidak valid
    }


    public function get($id)
    {
        // Dapat data menggunakan task_id
        $task = $this->Mtask->get($id);

        if ($task) { // Kondisi ketika task ditemukan dan deadline_status di tambahkan
            $today = date('Y-m-d');
            $deadline = $task['deadline'];

            if ($today < $deadline) {
                $deadline_status = 'Tepat Waktu';
            } elseif ($today == $deadline) {
                $deadline_status = 'Hari Deadline';
            } else {
                $deadline_status = 'Telat';
            }

            // Tambahkan deadline_status ke dalam data task
            $task['deadline_status'] = $deadline_status;

            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Task Berhasil Ditemukan',
                'Data' => [
                    'task_id' => $task['task_id'],
                    'user_id' => $task['user_id'],
                    'title' => $task['title'],
                    'description' => $task['description'],
                    'status_task' => $task['status'],
                    'deadline' => $task['deadline'],
                    'deadline_status' => $task['deadline_status'],
                    'created_at' => $task['created_at'],
                    'updated_at' => $task['updated_at']
                ]
            ]));
        } else { // Kondisi ketika task tidak ditemukan
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Task Tidak Ditemukan'
            ]));
        }
    }

    public function getFilter($id)
    {
        $user = $this->Mtask->getUserid($id);
        if ($user) {
            // Parameter
            $filters = [
                'user_id' => $id,
                'title' => $this->input->get('title'),
                'description' => $this->input->get('description'),
                'status' => $this->input->get('status'),
                'deadline_from' => $this->input->get('deadline_from'),
                'deadline_to' => $this->input->get('deadline_to'),
                'created_at' => $this->input->get('created_at')
            ];

            $tasks = $this->Mtask->getFilter($filters);

            if ($tasks) {
                foreach ($tasks as &$task) { // Kondisi deadline 
                    $today = date('Y-m-d');
                    $deadline = $task['deadline'];

                    if ($today < $deadline) {
                        $task['deadline_status'] = 'Tepat Waktu';
                    } elseif ($today == $deadline) {
                        $task['deadline_status'] = 'Hari Deadline';
                    } else {
                        $task['deadline_status'] = 'Telat';
                    }

                    // unset($task['deadline']); // Menghapus field deadline

                    $tasks_json[] = [
                        'task_id' => $task['task_id'],
                        'user_id' => $task['user_id'],
                        'title' => $task['title'],
                        'description' => $task['description'],
                        'status_task' => $task['status'],
                        'deadline' => $task['deadline'],
                        'deadline_status' => $task['deadline_status'],
                        'created_at' => $task['created_at'],
                        'updated_at' => $task['updated_at']
                    ];
                }

                $this->output->set_content_type('application/json')->set_output(json_encode([
                    'Status' => 'Success',
                    'Message' => 'Tasks Berhasil Ditemukan',
                    'Data' => $tasks_json
                ]));
            } else {
                $this->output->set_content_type('application/json')->set_output(json_encode([
                    'Status' => 'Success',
                    'Message' => 'Tasks Tidak Ditemukan'
                ]));
            }
        } else {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Tasks Tidak Ditemukan'
            ]));
        }
    }
    public function getAllFilter()
    {
        // Parameter
        $filters = [
            'title' => $this->input->get('title'),
            'description' => $this->input->get('description'),
            'status' => $this->input->get('status'),
            'deadline_from' => $this->input->get('deadline_from'),
            'deadline_to' => $this->input->get('deadline_to'),
            'created_at' => $this->input->get('created_at')
        ];

        $tasks = $this->Mtask->getFilter($filters);

        if ($tasks) {
            foreach ($tasks as &$task) { // Kondisi deadline 
                $today = date('Y-m-d');
                $deadline = $task['deadline'];

                if ($today < $deadline) {
                    $task['deadline_status'] = 'Tepat Waktu';
                } elseif ($today == $deadline) {
                    $task['deadline_status'] = 'Hari Deadline';
                } else {
                    $task['deadline_status'] = 'Telat';
                }

                // unset($task['deadline']); // Menghapus field deadline

                $tasks_json[] = [
                    'task_id' => $task['task_id'],
                    'user_id' => $task['user_id'],
                    'title' => $task['title'],
                    'description' => $task['description'],
                    'status_task' => $task['status'],
                    'deadline' => $task['deadline'],
                    'deadline_status' => $task['deadline_status'],
                    'created_at' => $task['created_at'],
                    'updated_at' => $task['updated_at']
                ];
            }

            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Tasks Berhasil Ditemukan',
                'Data' => $tasks_json
            ]));
        } else {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Tasks Tidak Ditemukan'
            ]));
        }
    }


    // public function getParamUserId()
    // {
    //     // Parameter
    //     $user_id = $this->input->get('user_id');
    //     if ($user_id) {
    //         $user = $this->Mtask->getUserid($user_id);
    //         if ($user) { // Kondisi ketika user_id ditemukan
    //             $filter = [];
    //             foreach ($user as $users) {
    //                 $today = date('Y-m-d');
    //                 $deadline = $users['deadline'];

    //                 if ($today < $deadline) {
    //                     $users['deadline_status'] = 'Tepat Waktu';
    //                 } elseif ($today == $deadline) {
    //                     $users['deadline_status'] = 'Hari Deadline';
    //                 } else {
    //                     $users['deadline_status'] = 'Telat';
    //                 }

    //                 $filter[] = [
    //                     'task_id' => $users['task_id'],
    //                     'user_id' => $users['user_id'],
    //                     'title' => $users['title'],
    //                     'description' => $users['description'],
    //                     'status_task' => $users['status'],
    //                     'deadline' => $users['deadline'],
    //                     'deadline_status' => $users['deadline_status'],
    //                     'created_at' => $users['created_at'],
    //                     'updated_at' => $users['updated_at']
    //                 ];
    //             }

    //             $this->output->set_content_type('application/json')->set_output(json_encode([
    //                 'Status' => 'Success',
    //                 'Message' => 'Task Berhasil Ditemukan',
    //                 'Data' => $filter
    //             ]));
    //         } else { // Kondisi ketika user tidak ditemukan
    //             $this->output->set_content_type('application/json')->set_output(json_encode([
    //                 'Status' => 'Error',
    //                 'Message' => 'Task Tidak Ditemukan'
    //             ]));
    //         }
    //     }
    // }

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
            'status' => $this->input->post('status'),
            'deadline' => $this->input->post('deadline')
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
        $this->form_validation->set_rules('deadline', 'Deadline', 'required|callback_validate_date', [
            'required' => 'Deadline Harus Diisi',
            'validate_date' => 'Format Tanggal Tidak Sesuai, gunakan format YYYY-MM-DD / YY-M-D'
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
                    'Data' => [
                        'user_id' => $data['user_id'],
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'status_task' => $data['status'],
                        'deadline' => $data['deadline']
                    ]
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
                'Message' => 'Task Berhasil Dihapus'
            ]));
        } else { //Kondisi ketika task gagal dihapus
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Error',
                'Message' => 'Gagal Menghapus Task'
            ]));
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
                'Message' => 'Gagal Menambahkan Comment'
            ]));
        }
    }

    // public function get_comment($task_id)
    // { 
    //     // Dapat comment menggunakan task_id
    //     $task = $this->Mtask->get($task_id);
    //     if (!$task) { // Kondisi ketika task tidak ditemukan
    //         $this->output->set_content_type('application/json')
    //             ->set_output(json_encode([
    //                 'Status' => 'Error',
    //                 'Message' => 'Task Tidak Ditemukan'
    //             ]));
    //         return;
    //     }
    //     $comments = $this->Mcomment->get($task_id);
    //    if ($comments) { // Kondisi ketika task ditemukan
    //     $this->output->set_content_type('application/json')
    //         ->set_output(json_encode([
    //             'Status' => 'Success',
    //             'Message' => 'Comment Berhasil Ditemukan',
    //             'Data' => $comments
    //         ]));
    // } else { // Kondisi ketika task tidak ditemukan
    //     $this->output->set_content_type('application/json')
    //         ->set_output(json_encode([
    //             'Status' => 'Error',
    //             'Message' => 'Comments Tidak Ditemukan']));
    // }
    // }
    public function get_comment($id)
    {
        $task = $this->Mcomment->get($id);
        if ($task) {
            // Parameter
            $filters = [
                'task_id' => $id,
                'user_id' => $this->input->get('user_id'),
                'username' => $this->input->get('username'),
                'name' => $this->input->get('name'),
                'comment' => $this->input->get('comment')
            ];

            $comments = $this->Mcomment->getFilter($filters);
            if (!empty($comments)) {
                $comment = [];
                foreach ($comments as $comment) {
                    $comment_json[] = [
                        'comment_id' => $comment['comment_id'],
                        'task_id' => $comment['task_id'],
                        'user_id' => $comment['user_id'],
                        'name' => $comment['name'],
                        'username' => $comment['username'],
                        'comment' => $comment['comment'],
                        'created_at' => $comment['created_at']
                    ];
                }

                $this->output->set_content_type('application/json')->set_output(json_encode([
                    'Status' => 'Success',
                    'Message' => 'Comments Berhasil Ditemukan',
                    'Data' => $comment_json
                ]));
            } else {
                $this->output->set_content_type('application/json')->set_output(json_encode([
                    'Status' => 'Success',
                    'Message' => 'Comments Tidak Ditemukan'
                ]));
            }
        } else { // Kondisi ketika task tidak ditemukan
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Comments Tidak Ditemukan'
                ]));
        }
    }
    public function get_comments()
    {
        // Parameter
        $filters = [
            'user_id' => $this->input->get('user_id'),
            'username' => $this->input->get('username'),
            'name' => $this->input->get('name'),
            'comment' => $this->input->get('comment')
        ];

        $comments = $this->Mcomment->getFilter($filters);
        if (!empty($comments)) {
            $comment = [];
            foreach ($comments as $comment) {
                $comment_json[] = [
                    'comment_id' => $comment['comment_id'],
                    'task_id' => $comment['task_id'],
                    'user_id' => $comment['user_id'],
                    'name' => $comment['name'],
                    'username' => $comment['username'],
                    'comment' => $comment['comment'],
                    'created_at' => $comment['created_at']
                ];
            }

            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Comments Berhasil Ditemukan',
                'Data' => $comment_json
            ]));
        } else {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Comments Tidak Ditemukan'
            ]));
        }
    }
}