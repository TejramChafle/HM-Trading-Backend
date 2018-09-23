<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Login_model extends CI_Model {

    function sign_in($data) {
        /* echo '<pre>';
        print_r($data);
        echo '</pre>'; */
        $this->load->database();
        $data['password'] = md5($data['password']);
        
        $query = $this->db->get_where('system_users', $data);

        /* echo '<pre>';
        print_r($query->row_array());
        echo '</pre>'; */
        // exit;
        return $result = $query->row_array();       
    }

}
