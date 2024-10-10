<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class StatusTokenHook{
    
    public function check_expired_tokens() {
        $CI =& get_instance(); // Mendapatkan instance CodeIgniter
        $CI->load->model('Mtoken'); // Memuat model Mtoken

        // Panggil fungsi pengecekan token kadaluarsa
        $CI->Mtoken->token_status();
    }
}
