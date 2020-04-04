<?php
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
        $data['username'] = 'background_user';
        $data['password'] = md5('background_password');
        $data['name'] = 'background_user_name';
        $data['email_id'] = 'background_user@example.com';
        $data['phone_no'] = 9730226518;
        $data['role'] = 1;
        $this->db->insert('system_users', $data);
    }


    /*---------------------------------------------------------------------------------------
        : SEND NOTIFICATION to pending installments
    ----------------------------------------------------------------------------------------*/
    function send_notification_to_pending_installments($params = array()) {
        $this->load->database();

        if (!isset($params['scheme_installment_id'])) {
            $month = date('F');
            $this->db->where('month', $month);
            $query = $this->db->get('scheme_installment');    
            $params['scheme_installment_id'] = $query->row_array()['scheme_installment_id'];
        } else {
            $this->db->where('scheme_installment_id', $params['scheme_installment_id']);
            $query = $this->db->get('scheme_installment');    
            $month = $query->row_array()['month'];
        }

        $this->db->select('customer_id');
        $this->db->where('scheme_installment_id', $params['scheme_installment_id']);
        $query = $this->db->get('customer_installments');

        $paid_customer_ids = $query->result_array();

        $this->db->where_not_in('customer_id', array_column($paid_customer_ids, 'customer_id'));
        $this->db->where('card_number IS NOT NULL', null, false);
        $this->db->where('isActive', '1');
        $this->db->order_by("agent_id", "asc");
        $query = $this->db->get('customer');

        $messages = array();
        foreach($query->result_array() as $customer) {

            if (isset($customer['mobile_number'])) {

                $msisdn  =  $customer['mobile_number'];
                $name = $customer['name'];

                // Replace with your Message content
                $msg = "Dear ".$name.", you have missed the installment payment of ".$month." for card ".$customer['card_number'].". The delay could impose a fine of Rs 5. Contact for help on +919765737487.";

                // Send message from send message service
                $this->load->model('Sendsms_model');
                $result = $this->Sendsms_model->send_sms($msg, $msisdn);
                array_push($messages, $result);
            }
        }
        return $messages;
        // return $query->result_array();
    }

}
?>