<?php

class Mtoken extends CI_Model {

    public function post($data)
    {
        return $this->db->insert('tokens', $data);
    }

    public function update($userId, $data)
    {
        $this->db->where('user_id', $userId);
        return $this->db->update('tokens', $data);
    }

    public function getToken($token) {
        return $this->db->get_where('tokens', ['token' => $token])->row_array();
    }

    public function getTokenByUserId($userId) {
        return $this->db->order_by('created_at', 'DESC') 
                        ->get_where('tokens', ['user_id' => $userId])
                        ->row_array(); 
    }
    public function token_status() {

        $this->db->where('expired_token <', date('Y-m-d H:i:s'));
        $this->db->where('status', 1); 
        $query = $this->db->get('tokens');

        if ($query->num_rows() > 0) {
            $expired_tokens = $query->result();

            foreach ($expired_tokens as $token) {
                $this->db->where('id', $token->id);
                $this->db->update('tokens', array('status' => 0));
            }
        }
    }
}

