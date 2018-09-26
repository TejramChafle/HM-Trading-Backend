<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Sendsms_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }


    // Add the item details in db
    function send_sms($msg, $msisdn) {
        // SmsIndiaHub message implementation
        /*$sid  =  "HMTRAD"; 
        $user = "HMTrading";
        $password = "mailme24hr"; 
        $msg = urlencode($msg);
        
        $fl = "0";
        $type   =  "txt";
        $ch = curl_init("http://cloud.smsindiahub.in/vendorsms/pushsms.aspx?user=".$user."&password=".$password."&msisdn=".$msisdn."&sid=".$sid."&msg=".$msg."&fl=".$fl); 
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);      
        curl_close($ch); */


        // Textlocal message implementation

        // Account details
        $apiKey = urlencode('f3d3a31b285f4c931ddede0acae738986364a40046e38344bc98045c3800e798');
        
        // Message details
        $numbers = array($msisdn);
        $sender  = urlencode('TXTLCL');
        $message = rawurlencode($msg);
        $numbers = implode(',', $numbers);
     
        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
     
        // Send the POST request with cURL
        $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Process your response here
        // echo $response;      
    }

}
