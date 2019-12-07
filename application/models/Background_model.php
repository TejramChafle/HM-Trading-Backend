<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Background_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }


    // Add the item details in db
    function send_sms() {

        // Account details
        $apiKey = urlencode('9FUGpx76Lv4-co6Tv7eY1O585eevwfyasTQu3ind1N');
        
        // // Message details
        // $numbers = array('91'+$msisdn);
        $sender  = urlencode('HMTRAD');

        $message = 'Dear Tejram Chafle, HM Trading has confirmed your loan for the amount of Rs. 1200. Contact for help on: +919765737487.';
        $message = rawurlencode($message);
        // $numbers = implode(',', $numbers);

        $numbers = '919730226518';
     
        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
     
        // Send the POST request with cURL
        $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

    }

    function background_cron() {
        $this->load->database();
        $data = array();
        // $data['username'] = 'background_user';
        $data['password'] = md5('background_password');
        $data['name'] = 'background_user_name';
        $data['email_id'] = 'background_user@example.com';
        $data['phone_no'] = 9730226518;
        $data['role'] = 1;
        $this->db->insert('system_users', $data);
    }

}
?>