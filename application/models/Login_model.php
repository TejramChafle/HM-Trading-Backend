<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Login_model extends CI_Model {

    function sign_in($data) {
        /* echo '<pre>';
        print_r($data);
        echo '</pre>'; */
        $this->load->database();
        $password = md5($data['password']);
        // $query = $this->db->get_where('system_users', $data);

        $this->db->where('username', $data['username']);
        $this->db->where('password', $password);
        $query = $this->db->get('system_users');

        /* echo '<pre>';
        print_r($query->row_array());
        echo '</pre>'; */
        // exit;
        return $result = $query->row_array();       
    }

}
