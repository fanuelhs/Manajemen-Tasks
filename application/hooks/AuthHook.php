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

       
        $controller = $CI->router->class;  
        $method = $CI->router->method;     
        if ($controller == 'Users' && ($method == 'login' || $method == 'create')) { // Kondisi ketika menggunakan method login dan create
            return;  // Untuk mengabaikan authenticate 
        }

        $token = $CI->input->get_request_header('Authorization');
        if (!$token) {// Kondisi ketika token tidak ada 
            $CI->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Token Tidak Ditemukan'
                ]))
                ->_display();
            exit;
        }

        list($jwt) = sscanf($token, 'Bearer %s');
        if (!$jwt) {  // Kondisi ketika token bukan JWT
            $CI->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Token Tidak Valid'
                ]))
                ->_display();
            exit;
        }

        $user = $CI->Muser->getToken($jwt);
        if (!$user) {  // Kondisi ketika token tidak valid
            $CI->output->set_content_type('application/json')
                ->set_output(json_encode([
                    'Status' => 'Error',
                    'Message' => 'Token Tidak Valid'
                ]))
                ->_display();
            exit;
        }

        if ($user['expired_token'] < date('Y-m-d H:i:s')) { // Kondisi ketika token kadaluarsa
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
