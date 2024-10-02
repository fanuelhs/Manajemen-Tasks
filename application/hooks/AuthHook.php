<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/jwt/JWT.php';
require_once APPPATH . 'libraries/jwt/Key.php';
require_once APPPATH . 'libraries/jwt/SignatureInvalidException.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

class AuthHook {

    private $key = "secret_key";

    public function authenticate()
    {
        $CI =& get_instance();
        $CI->load->model('Muser');

        // Mengecualikan method login
        $controller = $CI->router->class;  // Mendapatkan nama controller yang sedang berjalan
        $method = $CI->router->method;     // Mendapatkan nama method yang sedang berjalan

        // Cek apakah method saat ini adalah login, jika ya, abaikan autentikasi
        if ($controller == 'Users' && ($method == 'login' || $method == 'create')) {
            return;  // Jangan autentikasi pada fungsi login
        }

        // Lanjutkan proses autentikasi untuk method lain
        $token = $CI->input->get_request_header('Authorization');
        if (!$token) {
            $CI->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Token Tidak Ditemukan'
                ]))
                ->_display();
            exit;
        }

        list($jwt) = sscanf($token, 'Bearer %s');
        if (!$jwt) {
            $CI->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Token Tidak Valid'
                ]))
                ->_display();
            exit;
        }

        // Ambil user berdasarkan token dari database
        $user = $CI->Muser->getToken($jwt);
        if (!$user) {
            $CI->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Token Tidak Valid'
                ]))
                ->_display();
            exit;
        }

        // Periksa apakah token sudah kadaluarsa
        if ($user['expired_token'] < date('Y-m-d H:i:s')) {
            $CI->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Token Kadaluarsa'
                ]))
                ->_display();
            exit;
        }
    }
}
