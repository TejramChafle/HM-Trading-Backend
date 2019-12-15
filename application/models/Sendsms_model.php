<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Sendsms_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }


    
    /*---------------------------------------------------------------------------------------
        : Textlocal message implementation [This is working code copied from text local API]
    ----------------------------------------------------------------------------------------*/
    function send_sms($msg, $msisdn) {

        /*// Account details
        $apiKey = urlencode('9FUGpx76Lv4-co6Tv7eY1O585eevwfyasTQu3ind1N');

        // Message detail
        $sender  = urlencode('HMTRAD');
        $message = rawurlencode($msg);
        $numbers = '91'.$msisdn;
        // $numbers = implode(',', $numbers);
     
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
        echo $response;*/
    }





    /*---------------------------------------------------------------------------------------
        : GET message deleivery report from text-local server
    ----------------------------------------------------------------------------------------*/
    function messages_report () {
            // Account details
            $apiKey = urlencode('9FUGpx76Lv4-co6Tv7eY1O585eevwfyasTQu3ind1N');
         
            // Prepare data for POST request
            $data = array('apikey' => $apiKey);
         
            // Send the POST request with cURL
            $ch = curl_init('https://api.textlocal.in/get_history_api/');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            
            // Process your response here
            return $response;
    }





    /*---------------------------------------------------------------------------------------
        : GET message credit available from textlocal.in
    ----------------------------------------------------------------------------------------*/
    function check_message_credit() {
        // Authorisation details.   
        $username = "tejram_chafle@yahoo.in";
        $hash = "f3d3a31b285f4c931ddede0acae738986364a40046e38344bc98045c3800e798";
        
        // You shouldn't need to change anything here.  
        $data = "username=".$username."&hash=".$hash;
        $ch = curl_init('http://api.textlocal.in/balance/?');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $credits = curl_exec($ch);
        // This is the number of credits you have left  
        curl_close($ch);
        // echo $credits;
        return $credits;
    }

}
