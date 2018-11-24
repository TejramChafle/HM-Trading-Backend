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
        $apiKey = urlencode('9FUGpx76Lv4-co6Tv7eY1O585eevwfyasTQu3ind1N');
        
        // // Message details
        // $numbers = array('91'+$msisdn);
        $sender  = urlencode('HMTRAD');

        // $message = 'Dear Tejram Chafle Cname, HM Trading has confirmed your loan for the amount of Rs. 1200 Lamount. Contact for help on: +919765737487.';
        $message = rawurlencode($msg);
        // $numbers = implode(',', $numbers);

        $numbers = '91'.$msisdn;
     
        // Prepare data for POST request
        $data = array('apikey' => $apiKey, 'numbers' => $numbers, "sender" => $sender, "message" => $message);
     
        // Send the POST request with cURL
        $ch = curl_init('https://api.textlocal.in/send/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // // Process your response here
        // echo $response;      

        // Authorisation details.
        // $username = "tejram_chafle@yahoo.in";
        // $hash = "9FUGpx76Lv4-co6Tv7eY1O585eevwfyasTQu3ind1N";

        // // Config variables. Consult http://api.textlocal.in/docs for more info.
        // $test = "0";

        // // Data for text message. This is the text message data.
        // $sender = "HMTRAD";
        // // $sender = "TXTLCL"; // This is who the message appears to be from.
        // $numbers = "919730226518"; // A single number or a comma-seperated list of numbers
        // // $message = "This is a test message from the PHP API script.";
        // // 612 chars or less
        // // A single number or a comma-seperated list of numbers
        // $message = urlencode($message);
        // $data = "username=".$username."&hash=".$hash."&message=".$message."&sender=".$sender."&numbers=".$numbers."&test=".$test;
        // $ch = curl_init('http://api.textlocal.in/send/?');
        // curl_setopt($ch, CURLOPT_POST, true);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // $response = curl_exec($ch); // This is the result from the API

        // echo "<pre>";
        // echo $msg;
        // print_r($data);
        // print_r($response);
        // echo "</pre>";

        // curl_close($ch);

        // Process your response here
        // echo $response;
    }

}
