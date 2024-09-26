<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Muser');
    }
    private function generateRandomToken($length = 10)
    {
        return bin2hex(random_bytes($length / 2));
    }
    public function create()
    {
        $this->form_validation->set_rules('username', 'Username', 'required', [ // Form Validation
            'required' => 'Username Harus Diisi',
        ]);
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]', [
        'required' => 'Email Harus Diisi',
        'valid_email' => 'Format Email tidak valid',
        'is_unique' => 'Email Sudah Digunakan'
        ]);
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]', [
            'required' => 'Password Harus Diisi',
            'min_length' => 'Minimal 6 Karakter Password'
        ]);
        if ($this->form_validation->run() == FALSE) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([ // Kondisi ketika validasi gagal
                    'Status' => 'Error',
                    'Message' => 'Validasi Gagal',
                    'Errors' => $this->form_validation->error_array()
                ]));
        } else {
            $data = [
                'username' => $this->input->post('username'),
                'email' => $this->input->post('email'),
                'password' => $this->input->post('password'),
            ];
            if (strpos($data['email'], '@gmail.com') === false) { // Kondisi ketika tidak ada @gmail.com
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode([
                        'Status' => 'Error',
                        'Message' => 'Email Harus Menggunakan Domain @gmail.com'
                    ]));
                return;
            }

            $insert = $this->Muser->post($data);
            if ($insert) { // Kondisi ketika user berhasil dibuat
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode([
                        'Status' => 'Success',
                        'Message' => 'User Berhasil Dibuat',
                        'Data' => $data
                    ]));
            } else { // Kondisi ketika user gagal dibuat
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode([
                        'Status' => 'Error',
                        'Message' => 'User Gagal Dibuat'
                    ]));
            }
        }
    }
    public function get($id)
    { // Dapat data menggunakan user_id
        $user = $this->Muser->get($id);
        if ($user) { // Kondisi ketika user ditemukan
            $this->output->set_content_type('application/json')->set_output(json_encode($user));
        } else { // Kondisi ketika user tidak ditemukan
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Message' => 'User Tidak Ditemukan'
            ]));
        }
    }

    public function login()
    {
        $this->form_validation->set_rules('email', 'Email', 'required', [
            'required' => 'Email Harus Diisi'
        ]);
        $this->form_validation->set_rules('password', 'Password', 'required', [
            'required' => 'Password Harus Diisi'
        ]);
        if ($this->form_validation->run() == FALSE) { // Kondisi ketika validasi gagal
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Validasi Gagal',
                    'Errors' => $this->form_validation->error_array()
                ]));
        } else {
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            if (strpos($email, '@gmail.com') === false) { // Kondisi ketika tidak ada @gmail.com
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode([
                        'Status' => 'Error',
                        'Message' => 'Email Harus Menggunakan Domain @gmail.com'
                    ]));
                return;
            }

            $user = $this->Muser->login($email);

            if ($user) {
                if ($password === $user['password']) { // Kondisi ketika password sesuai
                    $token = $this->generateRandomToken(10);
                    $this->output->set_content_type('application/json')
                        ->set_output(json_encode([
                            'Status' => 'Success',
                            'Message' => 'Login Berhasil',
                            'UserData' => [
                                'user_id' => $user['user_id'],
                                'username' => $user['username'],
                                'email' => $user['email'],
                                'token' => $token,
                            ]
                        ]));

                } else { // Kondisi ketika password salah
                    $this->output->set_content_type('application/json')
                        ->set_output(json_encode([
                            'Status' => 'Error',
                            'Message' => 'Email atau Password Salah',
                        ]));
                }
            } else { // Kondisi ketika email salah
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode([
                        'Status' => 'Error',
                        'Message' => 'Email atau Password Salah',
                    ]));
            }
        }

    }
}

