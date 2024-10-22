<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/jwt/JWT.php';
require_once APPPATH . 'libraries/jwt/Key.php';
require_once APPPATH . 'libraries/jwt/SignatureInvalidException.php';

use \Firebase\JWT\JWT;

class Users extends CI_Controller
{
    private $key = "secret_key";

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Muser');
        $this->load->model('Mtoken');
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

                    $this->session->set_userdata('user_id', $user['user_id']);
                    $this->Mtoken->OldToken($user['user_id']);

                    $payload = array(
                        "iss" => "localhost",
                        "aud" => "localhost",
                        "iat" => time(),
                        "nbf" => time(),
                        "exp" => time() + 3600,
                        "data" => array(
                            "user_id" => $user['user_id'],
                            "username" => $user['username'],
                            "email" => $user['email']
                        )
                    );

                    $jwt = JWT::encode($payload, $this->key, 'HS256');
                    $token = substr(hash('sha256', $jwt), 0, 50);

                    $Data = [
                        'user_id' => $user['user_id'],
                        'token' => $token,
                        'expired_token' => date('Y-m-d H:i:s', time() + 3600),
                        'status' => 1,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    $this->Mtoken->post($Data);

                    $this->output->set_content_type('application/json')
                        ->set_output(json_encode([
                            'Status' => 'Success',
                            'Message' => 'Login Berhasil',
                            'UserData' => $Data
                        ]));

                } else { // Kondisi ketika password salah
                    $this->output->set_content_type('application/json')
                        ->set_output(json_encode([
                            'Status' => 'Error',
                            'Message' => 'Password Salah',
                        ]));
                }
            } else { // Kondisi ketika email salah
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode([
                        'Status' => 'Error',
                        'Message' => 'Email Salah',
                    ]));
            }
        }
    }

    public function logout()
    {
        $user_id = $this->session->userdata('user_id');

        if ($user_id) {
            $this->Mtoken->update($user_id, [
                'status' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->session->unset_userdata('user_id');

            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Success',
                    'Message' => 'Logout Berhasil'
                ]));
        } else {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Tidak ada sesi aktif untuk logout'
                ]));
        }
    }

    // public function logout()// Logout with token
    // {
    //     $token = $this->input->get_request_header('Authorization');
    //     list($jwt) = sscanf($token, 'Bearer %s');

    //     if (!$jwt) {
    //         $this->output->set_content_type('application/json')
    //             ->set_output(json_encode([
    //                 'Status' => 'Error',
    //                 'Message' => 'Token Tidak Valid'
    //             ]))
    //             ->_display();
    //         exit;
    //     }

    //     $tokenData = $this->Mtoken->getToken($jwt);
    //     if ($tokenData) { // Kondisi ketika token ditemukan
    //         $this->Mtoken->update($tokenData['user_id'], [
    //             'status' => 0,
    //             'updated_at' => date('Y-m-d H:i:s'), 
    //         ]);

    //         $this->output->set_content_type('application/json')
    //             ->set_output(json_encode([
    //                 'Status' => 'Success',
    //                 'Message' => 'Logout Berhasil'
    //             ]));
    //     } else { // Kondisi ketika token tidak valid
    //         $this->output->set_content_type('application/json')
    //             ->set_output(json_encode([
    //                 'Status' => 'Error',
    //                 'Message' => 'Token Tidak Valid'
    //             ]));
    //     }
    // }

    // public function logout($userId = null) // Logout with user_id
    // {
    //     if (!$userId) {
    //         $this->output->set_content_type('application/json')
    //             ->set_output(json_encode([ // Kondisi ketika user_id tidak ditemukan
    //                 'Status' => 'Error',
    //                 'Message' => 'User ID Tidak Ditemukan'
    //             ]))
    //             ->_display();
    //         exit;
    //     }

    //     $this->Mtoken->update($userId, [
    //         'status' => 0,
    //         'updated_at' => date('Y-m-d H:i:s'), 
    //     ]);

    //     $this->output->set_content_type('application/json')
    //         ->set_output(json_encode([
    //             'Status' => 'Success',
    //             'Message' => 'Logout Berhasil'
    //         ]));
    // }


    public function create()
    {
        // Tambah user
        $this->form_validation->set_rules('firstname', 'Firstname', 'required', [ // Form Validation
            'required' => 'Firstname Harus Diisi',
        ]);
        $this->form_validation->set_rules('lastname', 'Lastname', 'required', [
            'required' => 'Lastname Harus Diisi',
        ]);
        $this->form_validation->set_rules('username', 'Username', 'required', [
            'required' => 'Username Harus Diisi',
        ]);
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]', [
            'required' => 'Email Harus Diisi',
            'valid_email' => 'Format Email Menggunakan Domain @gmail.com',
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
                'firstname' => $this->input->post('firstname'),
                'lastname' => $this->input->post('lastname'),
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
    // public function get($id)
    // {
    //     // Dapat data menggunakan user_id
    //     $user = $this->Muser->get($id);
    //     if ($user) { // Kondisi ketika user ditemukan
    //         $filter = [
    //             'user_id' => $user['user_id'],
    //             'username' => $user['username'],
    //             'email' => $user['email'],
    //             'password' => $user['password'],
    //             'created_at' => $user['created_at']
    //         ];

    //         $this->output->set_content_type('application/json')->set_output(json_encode([
    //             'Status' => 'Success',
    //             'Message' => 'User Berhasil Ditemukan',
    //             'Data' => $user
    //         ]));
    //     } else { // Kondisi ketika user tidak ditemukan
    //         $this->output->set_content_type('application/json')->set_output(json_encode([
    //             'Status' => 'Error',
    //             'Message' => 'User Tidak Ditemukan'
    //         ]));
    //     }
    // }
    public function get()
    {
        // Ambil parameter dari input (bisa dari query string atau POST)
        $filter = [
            'email' => $this->input->get('email'),      
            'username' => $this->input->get('username'),
            'name' => $this->input->get('name')         
        ];

        $users = $this->Muser->getFilter($filter);
        foreach ($users as &$user) {
            $user = [
                'user_id' => $user['user_id'],
                'name' => $user['firstname'] . ' ' . $user['lastname'],
                'username' => $user['username'],
                'email' => $user['email'],
                'password' => $user['password'],
                'created_at' => $user['created_at']
            ];
            unset($user['firstname']); // Menghapus field firstname 
            unset($user['lastname']);  // Menghapus field lastname 
        }

        if (!empty($users)) {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Success',
                'Message' => 'Users Berhasil Ditemukan',
                'Data' => $users
            ]));
        } else {
            $this->output->set_content_type('application/json')->set_output(json_encode([
                'Status' => 'Error',
                'Message' => 'Users Tidak Ditemukan'
            ]));
        }
    }
    public function getToken($userId)
    {
        $token = $this->Mtoken->getTokenByUserId($userId);
        if ($token) { // Kondisi ketika token ditemukan
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Success',
                    'Message' => 'Token Berhasil Ditemukan',
                    'Data' => $token
                ]));
        } else { // Kondisi ketika token tidak ditemukan
            $this->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Token Tidak Ditemukan'
                ]));
        }
    }
    public function getParamUserId()
    {
        // Dapat data menggunakan user_id
        $user_id = $this->input->get('user_id');
        if ($user_id) {
            // Dapatkan data user berdasarkan user_id
            $user = $this->Muser->get($user_id);
            if ($user) { // Kondisi ketika user ditemukan
                $filter = [
                    'user_id' => $user['user_id'],
                    'name' => $user['firstname'] . ' ' . $user['lastname'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'password' => $user['password'],
                    'created_at' => $user['created_at']
                ];

                $this->output->set_content_type('application/json')->set_output(json_encode([
                    'Status' => 'Success',
                    'Message' => 'User Berhasil Ditemukan',
                    'Data' => $filter
                ]));
            } else { // Kondisi ketika user tidak ditemukan
                $this->output->set_content_type('application/json')->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'User Tidak Ditemukan'
                ]));
            }
        }
    }
}

