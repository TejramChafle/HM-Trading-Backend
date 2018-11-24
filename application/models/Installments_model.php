<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Installments_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }


    // Get the list of all schemes 
    function get_installments($params = array()) {
        $offset = $params['offset'];
        $limit  = $params['limit'];
        $page   = $params['page'];

        unset($params['offset']);
        unset($params['limit']);
        unset($params['page']);

        $this->db->like($params);
        $this->db->order_by("card_number", "asc");

        $this->db->limit($limit, $offset);
        $this->db->where('isActive', '1');
        $query = $this->db->get('customer');
        $query_result = array();

        foreach($query->result_array() as $item) {

            $this->db->where('customer_id', $item['customer_id']);
            $ci_query = $this->db->get('customer_installments');
            $result = $ci_query->result_array();
            $item['installment'] = $result;  
            // $item['installment'] = array();


            $this->db->where('item_id', $item['item_id']);
            $item_query = $this->db->get('item');
            $result = $item_query->row_array();
            $item['item'] = $result;

            $this->db->where('customer_id', $item['agent_id']);
            $item_query = $this->db->get('customer');
            $result = $item_query->row_array();
            $item['agent'] = $result;  
            
            $i = 0;

            foreach($ci_query->result_array() as $insta) {

                $this->db->where('scheme_installment_id', $insta['scheme_installment_id']);

                $si_query = $this->db->get('scheme_installment');
                
                $result = $si_query->row_array();

                // echo '<pre>';
                // print_r($result);
                // echo '</pre>';
                $item['installment'][$i]['scheme_id'] = $result['scheme_id'];
                $item['installment'][$i]['month'] = $result['month'];
                $item['installment'][$i]['installment_date'] = $result['installment_date'];
                $item['installment'][$i]['installment_price'] = $result['installment_price'];
                $item['installment'][$i]['fine'] = $result['fine'];
                
                // array_push($item['installment'], $result);             
                // Increment index
                $i++;    
            }

            array_push($query_result, $item);
        }

        $return_result = array();
        
        $params['database'] = 'customer';
        $pagination = $this->pagination($params);
        $pagination['page'] = $page;
        $pagination['offset'] = $offset;
        
        $return_result['records'] = $query_result;
        $return_result['pagination'] = $pagination;

        return $return_result;
    }

    
    function update_installment($params = array()) {
        $params['paid_date'] = date('Y-m-d H:i:s');
        $params['last_modified_date'] = date('Y-m-d H:i:s'); 
        $this->db->where('installment_id', $params['installment_id']);
        $query = $this->db->update('customer_installments', $params);
        return $query;
    }



    // This function will add the installment for more than one customer to their last month
    function add_installment($params = array()) {

        $installment_ids = array();
        $installment_months = array();  
        
        foreach ($params as $key => $value) {

            $this->db->where('customer_id', $value['customer_id']);
            $query = $this->db->get('customer');
            $customer = $query->row_array();         
             
            $months = array();
            $fine = 0;
            $amount = 0;

            foreach ($value['installments'] as $inst) {
                $input = array();
                $input['scheme_installment_id'] = $inst['scheme_installment_id'];
                $input['amount'] = $inst['amount'];
                $input['customer_id'] = $value['customer_id'];
                $input['paid_date'] = date('Y-m-d H:i:s');

                $installment_date = date('Y-m-d H:i:s', strtotime($inst['installment_date']));

                if( strtotime($input['paid_date']) > strtotime($inst['installment_date']) ) {

                    // As of now the fine applicable is removed by the client
                    // $input['paid_fine'] = $inst['fine'];
                    
                    $input['paid_fine'] = 0;  
                    $fine += $input['paid_fine'];   
                }

                $input['status'] = 'Paid';

                $amount += $input['amount'];
                

                // Now add the new record to customer_installment
                $query = $this->db->insert('customer_installments', $input);
                $insert_id = $this->db->insert_id();

                array_push($installment_ids, $insert_id);
                array_push($installment_months, $inst['month']);
                array_push($months, $inst['month']);
            } 

            if(isset($customer['mobile_number'])) {

                // Replace with the destination mobile Number to which you want to send sms
                $msisdn =  $customer['mobile_number'];
                $name   = $customer['name'];
                $month  = implode(",", $months);
                $installment = $amount;

                // Replace with your Message content
                $msg = "Dear $name, we have received an installment Rs $installment of the month $month.";

                if($fine!=0) {
                    $msg .= " A fine of Rs $fine is imposed on you for late payment.";              
                }

                $msg .= " Contact for help on : +919765737487.";

                // Send message from send message service
                $this->load->model('Sendsms_model');
                $this->Sendsms_model->send_sms($msg, $msisdn);
            }
        }


        // Enter into receipt table
        $receipt = array();
        $receipt['installment_ids'] = implode(",", $installment_ids);
        $receipt['receipt_date'] = date('Y-m-d H:i:s');
        isset($params[0]['agent']) ? $receipt['is_agent'] = 1 : $receipt['is_agent'] = 0;
        isset($params[0]['agent']) ? $receipt['customer_id'] = $params[0]['agent']['customer_id'] : $receipt['customer_id'] = $params[0]['customer_id'];
        $query = $this->db->insert('receipt', $receipt);
        $insert_id = $this->db->insert_id();

        return  $insert_id;
    }




    // Get the list of all installments of the selected customers 
    function get_installments_of_selected_customers($params = array()) {

        $ids = implode(",", $params);
        $sql = "SELECT * FROM customer WHERE customer_id IN (".$ids.")";
        $query = $this->db->query($sql);        
        $query_result = array();

        foreach($query->result_array() as $item) {

            $this->db->where('customer_id', $item['customer_id']);
            $ci_query = $this->db->get('customer_installments');
            $result = $ci_query->result_array();
            $item['installment'] = $result;  
            // $item['installment'] = array();


            $this->db->where('item_id', $item['item_id']);
            $item_query = $this->db->get('item');
            $result = $item_query->row_array();
            $item['item'] = $result;

            $this->db->where('customer_id', $item['agent_id']);
            $item_query = $this->db->get('customer');
            $result = $item_query->row_array();
            $item['agent'] = $result;  
            
            $i = 0;

            foreach($ci_query->result_array() as $insta) {

                $this->db->where('scheme_installment_id', $insta['scheme_installment_id']);
                $si_query = $this->db->get('scheme_installment');
                
                $result = $si_query->row_array();

                // echo '<pre>';
                // print_r($result);
                // echo '</pre>';
                $item['installment'][$i]['scheme_id'] = $result['scheme_id'];
                $item['installment'][$i]['month'] = $result['month'];
                $item['installment'][$i]['installment_date'] = $result['installment_date'];
                $item['installment'][$i]['installment_price'] = $result['installment_price'];
                $item['installment'][$i]['fine'] = $result['fine'];
                
                // array_push($item['installment'], $result);             
                // Increment index
                $i++;    
            }

            array_push($query_result, $item);
        }

        return $query_result;
    }


    function lucky_draw($params = array()) {
        
        if(isset($params['month'])) {
            $month = $params['month'];
        } else {
            $month = date('F');
        }

        $this->db->where('scheme_id', 1);
        $this->db->where('month', $month);
        $scheme_query = $this->db->get('scheme_installment');
        $scheme_query_result = $scheme_query->row_array();

        $this->db->select('*');
        $this->db->where('has_won_draw', 0);
        $this->db->from('customer');
        $this->db->where('scheme_installment_id', $scheme_query_result['scheme_installment_id']);
        $this->db->join('customer_installments', 'customer_installments.customer_id = customer.customer_id');
        $query = $this->db->get();
        $query_result = array();

        foreach($query->result_array() as $item) {

            $this->db->where('item_id', $item['item_id']);
            $item_query = $this->db->get('item');
            $result = $item_query->row_array();
            $item['item'] = $result;

            $this->db->where('customer_id', $item['agent_id']);
            $item_query = $this->db->get('customer');
            $result = $item_query->row_array();
            $item['agent'] = $result;

            array_push($query_result, $item);
        }

        return $query_result;
    }



    function save_lucky_customer($params = array()) {
        
        if(isset($params['month'])) {
            $month = $params['month'];
        } else {
            $month = date('F');
        }

        $this->db->where('customer_id', $params['customer_id']);
        $this->db->set('has_won_draw', 1);
        $this->db->set('lucky_draw_item', $params['item_id']);
        $this->db->set('lucky_draw_month', $month);
        $query = $this->db->update('customer');

        $this->db->where('item_id', $params['item_id']);
        $this->db->set('status', 'NA');
        $query = $this->db->update('item');


        // Get the item details
        $this->db->where('item_id', $params['item_id']);
        $query = $this->db->get('item');
        $item_result = $query->row_array();
        $item = $item_result['name'];


        $this->db->where('customer_id', $params['customer_id']);
        $query = $this->db->get('customer');
        $customer = $query->row_array();

        $winner = $customer['name'];
        $phone = $customer['mobile_number'];

        if(isset($customer['mobile_number'])) {
            
            $msisdn  =  $customer['mobile_number'];
            $name = $customer['name'];
            $msg = "Dear $name, congratulations! You are the lucky customer of HM Trading for $month and have won $item";

            // Send message from send message service
            $this->load->model('Sendsms_model');
            $this->Sendsms_model->send_sms($msg, $msisdn);
        }


        // Select all the customers to inform about the winner of the lucky draw
        $query = $this->db->get('customer');

        foreach ($query->result_array() as $customer) {
            if(isset($customer['mobile_number'])) {

                $msisdn  =  $customer['mobile_number'];
                $name = $customer['name'];

                // current month
                // $month = date('F');

                // Replace with your Message content
                $msg = "Dear $name, congratulate $winner for winning lucky customer draw of HM Trading for $month. He has won $item. Contact him on $phone.";

                // Send message from send message service
                $this->load->model('Sendsms_model');
                $this->Sendsms_model->send_sms($msg, $msisdn);
            }
        }

        return $output;
    }



    function lucky_customers() {

        // Select the customers who has not won 
        $this->db->where('has_won_draw', 1);
        $query = $this->db->get('customer');
        $lucky_customers = array();

        foreach($query->result_array() as $item) {

            $this->db->where('item_id', $item['lucky_draw_item']);
            $item_query = $this->db->get('item');
            $result = $item_query->row_array();
            $item['lucky_draw_item'] = $result;

            array_push($lucky_customers, $item);
        }

        return $lucky_customers;
    }


    function payments($params = array()) {

        $offset = $params['offset'];
        $limit  = $params['limit'];
        $page   = $params['page'];

        unset($params['offset']);
        unset($params['limit']);
        unset($params['page']);

        if(sizeof($params)){
            $this->db->like($params);
        }
        
        $this->db->order_by("receipt_date", "desc");
        $this->db->limit($limit, $offset);
        $query = $this->db->get('receipt');
        $payments = array();

        foreach($query->result_array() as $item) {

            $this->db->where('customer_id', $item['customer_id']);
            $query = $this->db->get('customer');
            $customer = $query->row_array();
            $item['customer'] = $customer;

            $this->db->where('customer_id', $customer['agent_id']);
            $query = $this->db->get('customer');
            $agent = $query->row_array();
            $item['agent'] = $agent;

            $installment_ids = explode(',', $item['installment_ids']);

            $data = array();
            $amount = 0;

            foreach ($installment_ids as $value) {
                $this->db->select('*');
                $this->db->where('installment_id', $value);
                $this->db->from('customer_installments');
                $this->db->join('scheme_installment', 'customer_installments.scheme_installment_id = scheme_installment.scheme_installment_id');
                $this->db->join('customer', 'customer_installments.customer_id = customer.customer_id');
                $query = $this->db->get();

                $amount += $query->row_array()['amount'] + $query->row_array()['paid_fine'];
                array_push($data, $query->row_array());
            }

            $item['payments'] = $params;
            $item['amount'] = $amount;

            array_push($payments, $item);
        }

        $return_result = array();
        $params['database'] = 'receipt';
        $pagination = $this->pagination($params);
        $pagination['page'] = $page;
        $pagination['offset'] = $offset;

        $return_result = array();
        $return_result['records'] = $payments;
        $return_result['pagination'] = $pagination;
        return $return_result;
    }



    // Get the list of all loan installments for the provided id 
    function get_loan_installments($params = array()) {
        
        /*$this->db->where('account_id', $params['account_id']);
        $this->db->order_by("installment_id", "asc");
        $query = $this->db->get('loan_installments');

        $result = array();
        $result['installments'] = $query->result_array();*/

        $this->db->where('account_id', $params['account_id']);
        $query = $this->db->get('loan_transactions');
        $transactions = $query->result_array();

        $this->db->where('account_id', $params['account_id']);
        $query = $this->db->get('loan_accounts');
        $result['account'] = $query->row_array();

        $balances = array();

        $result['account']['type'] == 'Loan' ? $previous_balance = $result['account']['amount'] : $previous_balance = 0;
        foreach ($transactions as $value) {
            $value['previous_balance'] = $previous_balance; 
            $previous_balance   = $value['balance'];
            $value['total']     = $value['amount'] + $value['interest_paid'] + $value['fine_paid'];
            array_push($balances, $value);
        }

        $result['transactions'] = $balances;

        $this->db->where('customer_id', $result['account']['customer_id']);
        $query = $this->db->get('loan_customers');
        $result['customer'] = $query->row_array();
        
        return $result;

        // $this->db->select('*');
        // $this->db->from('loan_transactions');
        // $this->db->join('loan_accounts','loan_accounts.account_id=loan_transactions.account_id');
        // $this->db->where('loan_accounts.type',$params['type']);
        // $this->db->where('loan_accounts.customer_id',$params['customer_id']);
        // // $this->db->group_by("loan_accounts.account_id");
        // $query = $this->db->get();
        // $transactions = $query->result_array();

        
    }



    // Get the list of all loan installments for the provided id 
    function get_installment_history($params = array()) {
        $response     = array();
        $response['records'] = array();

        $this->db->where('customer_id', $params['customer_id']);
        $query = $this->db->get('loan_accounts');
        $accounts = $query->result_array();
        
        foreach ($accounts as $key => $account) {
            $this->db->where('account_id', $account['account_id']);
            $query = $this->db->get('loan_transactions');
            $transactions = $query->result_array();

            $balances   = array();
            $result     = array();

            $account['type'] == 'Loan' ? $previous_balance = $account['amount'] : $previous_balance = 0;
            foreach ($transactions as $value) {
                $value['previous_balance'] = $previous_balance; 
                $previous_balance   = $value['balance'];
                $value['total']     = $value['amount'] + $value['interest_paid'] + $value['fine_paid'];
                array_push($balances, $value);
            }

            $result['account']      = $account;
            $result['transactions'] = $balances;
            array_push($response['records'], $result);
        }

        $this->db->where('customer_id', $result['account']['customer_id']);
        $query = $this->db->get('loan_customers');
        $response['customer'] = $query->row_array();
        
        return $response;
    }



    /*---------------------------------------------------------------------------------------
        : Initialize the pagination for records
    ----------------------------------------------------------------------------------------*/
    function pagination($params = array()) {
        $db = $params['database'];
        unset($params['database']);

        if(sizeof($params)) {
            $this->db->like($params);
        }

        $this->db->where('isActive', '1');
        $query = $this->db->get($db);
        $rowcount = $query->num_rows();

        $result = array();
        $result['size'] = $rowcount;
        return $result;
    }

}
