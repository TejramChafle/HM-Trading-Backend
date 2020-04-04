<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Customer_model extends CI_Model {
    public $hasMoreInstallments = 10;
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
    }


    /*---------------------------------------------------------------------------------------
        : Initialize the pagination for records
    ----------------------------------------------------------------------------------------*/
    function init_pagination($data) {
        if(sizeof($data)){
            $this->db->like($data);
        }

        $query = $this->db->get($data['table']);

        $result['total_records'] = sizeof($query->row_array());

        $total_pages = $result['total_records'] / $data['page_size'];
        $record_remain = $result['total_records'] % $data['page_size'];

        $record_remain > 1 ?  $result['total_pages'] = $total_pages + 1 : $result['total_pages'] = $total_pages;

        return $result;
    }

    /*---------------------------------------------------------------------------------------
        : This function saves the customer record into the db
    ----------------------------------------------------------------------------------------*/
    function add_customer($data) {

        try {
            if(isset($data['customer_id'])) {
                $this->db->where('customer_id', $data['customer_id']);
                $data['last_modified_date'] = date('Y-m-d H:i:s');
                $query = $this->db->update('customer', $data);
                return $query;
            } else {

                // This will be required when initial amount paid will be stored in installments
                $installment_ids = array();

                if (isset($data['card_number'])) {
                    // Check if the card exists in active accounts
                    $this->db->where('isActive', 1);
                    $this->db->where('card_number', $data['card_number']);
                    $query = $this->db->get('customer');
                    $person = $query->row_array();

                    if (isset($person) && is_array($person)) {
                        return "Error:The card number ".$data['card_number']." is already taken by ".$person['name'].". Please enter other card number.";
                    }
                }

                // Save the customer information in db
                $data['created_date'] = date('Y-m-d H:i:s');
                $data['last_modified_date'] = date('Y-m-d H:i:s');
                $customer = $this->db->insert('customer', $data);
                $customer_id = $this->db->insert_id();

                // Now send the text message if the customer mobile number is provided
                if(isset($data['mobile_number'])) {
                    $msg = '';
                    $msisdn  =  $data['mobile_number'];
                    $name = $data['name'];

                    if(isset($data['item_id'])) {

                        $this->db->where('item_id', $data['item_id']);
                        $query = $this->db->get('item');
                        $result = $query->row_array();
                        $item = $result['name'];

                        $msg = "Dear $name, you have been successfully registered for the HM Trading Fataka Fund Scheme with item $item. Contact for help on : +919765737487.";
                    } else {
                        $msg = "Dear $name, you have been successfully registered for the HM Trading Fataka Fund Scheme as an agent. Contact for help on : +919765737487.";
                    }

                    // Send message from send message service
                    $this->load->model('Sendsms_model');
                    $this->Sendsms_model->send_sms($msg, $msisdn);
                }


                // If the initial amount is paid my customer then save the information into scheme installment table
                if (isset($data['down_payment']) && !is_null($data['down_payment']) && $data['down_payment'] > 0) {
                    // Here we'll send scheme id 
                    // $this->db->where('scheme_id', 1);
                    $this->db->where('isActive', '1');
                    $query = $this->db->get('scheme_installment');
                    $input = array();

                    // Flag to check if the amount is available to add in customer installment table
                    $hasMoreInstallments = TRUE;

                    isset($data['down_payment']) &&  !is_null($data['down_payment']) ? $down_payment = $data['down_payment'] : $down_payment = 0;

                    foreach($query->result_array() as $item) {

                        $input['scheme_installment_id'] = $item['scheme_installment_id'];
                        $input['customer_id'] = $customer_id;
                        $input['created_date'] = date('Y-m-d H:i:s');
                        $input['last_modified_date'] = date('Y-m-d H:i:s');

                        if(isset($data['down_payment']) && $data['down_payment'] > 0 ){

                            $input['paid_date'] = date('Y-m-d H:i:s');
                            $input['comment']   = 'Paid this installment with the first payment of Rs. '.$down_payment;

                            if($data['down_payment'] >= $item['installment_price'] ) {
                                $input['amount']    = $item['installment_price'];
                                $input['status']    = 'Paid';

                                $today = date('Y-m-d');
                                // if( strtotime($today) > strtotime($item['installment_date']) ){
                                //     $input['paid_fine'] = 5;               
                                // }    
                                $query = $this->db->insert('customer_installments', $input);
                                $insert_id = $this->db->insert_id();
                                array_push($installment_ids, $insert_id);

                                if (isset($data['mobile_number'])) {
                                    // Replace with your Message content
                                    $msg = "Dear ".$data['name'].", we have received an installment Rs ".$input['amount']." of the month ". $item['month'].".";

                                    $msg .= " Contact for help on : +919765737487.";

                                    // Send message from send message service
                                    $this->load->model('Sendsms_model');
                                    $this->Sendsms_model->send_sms($msg, $data['mobile_number']);
                                }
                            } else {
                                $input['amount']        = $data['down_payment'];
                                $input['status']        = 'Pending';
                                $query = $this->db->insert('customer_installments', $input);
                                break;
                                // $hasMoreInstallments    = FALSE;
                            }

                            $data['down_payment'] -= $item['installment_price'];
                        }
                    }

                    // Enter into receipt table
                    $receipt = array();
                    $receipt['installment_ids'] = implode(",", $installment_ids);
                    $receipt['receipt_date'] = date('Y-m-d H:i:s');
                    isset($data['agent_id']) ? $receipt['is_agent'] = 1 : $receipt['is_agent'] = 0;
                    isset($data['agent_id']) ? $receipt['customer_id'] = $data['agent_id'] : $receipt['customer_id'] = $customer_id;
                    $query = $this->db->insert('receipt', $receipt);
                    $insert_id = $this->db->insert_id();
                    return  $insert_id;
                } else {
                    return $customer_id;
                }
            }
        } catch (Exception $e) {
            log_message('error',$e->getMessage());
            $this->exceptionhandler->handle($e);
        }
    }


    /*---------------------------------------------------------------------------------------
        : This function brings the customer records from the db
    ----------------------------------------------------------------------------------------*/
    function get_customers($data) {
        $return_result = array();

        $offset = $data['offset'];
        $limit  = $data['limit'];
        $page   = $data['page'];

        if (isset( $data['item_id'])) {
            $item_id= $data['item_id'];
            unset($data['item_id']);
        }
        if (isset($data['card_number'])) {
            $card_number = $data['card_number'];
            unset($data['card_number']);
        }
        if (isset($params['agent_id'])) {
            $agent_id = $params['agent_id'];
            unset($params['agent_id']);
        }

        unset($data['offset']);
        unset($data['limit']);
        unset($data['page']);

        // PAGINATION
        if (isset( $item_id)) {
            $this->db->where('item_id', $item_id);
        }
        if (isset($card_number)) {
            $this->db->where('card_number', $card_number);
        }
        if (isset( $agent_id)) {
            $this->db->where('agent_id', $agent_id);
        }
        if (sizeof($data)) {
            $this->db->like($data);
        }
        // Get only active records
        $this->db->where('isActive', 1);
        $this->db->order_by("customer_id", "desc");
        $size = $this->db->count_all_results('customer');

        $pagination = array();
        $pagination['size'] = $size;
        $pagination['page'] = $page;
        $pagination['offset'] = $offset;


        // SEARCH RESULT
        if (isset( $item_id)) {
            $this->db->where('item_id', $item_id);
        }
        if (isset($card_number)) {
            $this->db->where('card_number', $card_number);
        }
        if (isset( $agent_id)) {
            $this->db->where('agent_id', $agent_id);
        }
        if (sizeof($data)) {
            $this->db->like($data);
        }
        // Get only active records
        $this->db->where('isActive', 1);
        if (isset( $agent_id)) {
            $this->db->order_by("card_number", "asc");
        } else {
            $this->db->order_by("customer_id", "desc");    
        }
        $this->db->limit($limit, $offset);
        $query = $this->db->get('customer');

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
        
        $return_result['records'] = $query_result;
        $return_result['pagination'] = $pagination;
        return $return_result;
    }



    // Get the list of all items 
    function get_items() {
        $query = $this->db->get('item');
        return $query->result_array();
    }


    // Get customer details of the provided id
    function get_customer_detail($params = array()) {
        $this->db->where('customer_id', $params['customer_id']);
        $this->db->order_by("card_number", "asc");
        $query = $this->db->get('customer');
        // echo "<pre>";
        // print_r($query->row_array());
        // echo "</pre>";
        return $query->row_array();
    }


    // Delete customer details of the provided id
    function delete_customer_detail($params = array()) {
        // Update customer table instead of deleting the records
        $params['isActive'] = 0;
        $this->db->where('customer_id', $params['customer_id']);
        $query = $this->db->update('customer', $params);
        return $query;
    }


    // Get customer details of the provided id
    function get_item_detail($params = array()) {
        $this->db->where('item_id', $params['item_id']);
        $query = $this->db->get('item');
        // echo "<pre>";
        // print_r($query->row_array());
        // echo "</pre>";
        return $query->row_array();
    }


    // Delete customer details of the provided id
    function delete_item($params = array()) {
        $query = $this->db->delete('item', $params);
        return $query;
    }


    // Add the item details in db
    function add_item($data) {
        try {
            if(isset($data['item_id'])) {
                $this->db->where('item_id', $data['item_id']);
                $query = $this->db->update('item', $data);
                return $query;
            } else {
                $query = $this->db->insert('item', $data);
                return $query;
            }
        } catch (Exception $e) {
            log_message('error',$e->getMessage());
            $this->exceptionhandler->handle($e);
        }
    }


    // Get the list of schemes
    function get_schemes() {
        $query = $this->db->get('scheme');
        return $query->result_array();
    }











/*  -------------------------------------------------------------------------------------------
    | The following functions deals with the Loan module of the project
    -------------------------------------------------------------------------------------------  */ 

    function add_loan_customer($data) {
        try {
            if(isset($data['customer_id'])) {
                unset($data['type']);
                $this->db->where('customer_id', $data['customer_id']);
                $data['last_modified_date'] = date('Y-m-d H:i:s');
                $query = $this->db->update('loan_customers', $data);
            } else {

                $input = array();
                $trans = array();
                $input['type'] = $data['type'];
                unset($data['type']);

                if( isset($data['amount']) ) {
                    $trans['amount'] = $data['amount'];
                    $trans['balance'] = $data['amount'];
                    unset($data['amount']);
                }
                

                $data['created_date'] = date('Y-m-d H:i:s');
                $data['last_modified_date'] = date('Y-m-d H:i:s');
                $query = $this->db->insert('loan_customers', $data);
                $customer_id = $this->db->insert_id();
                
                // unset($data['account_number']);
                

                // Create Saving account
                $input['customer_id'] = $customer_id;
                $input['interest_fine'] = 50; // Rs 50 for the late payment
                $input['created_date'] = date('Y-m-d H:i:s');
                $input['last_modified_date'] = date('Y-m-d H:i:s');
                $query = $this->db->insert('loan_accounts', $input);
                $account_id = $this->db->insert_id();

                
                $trans['customer_id']   = $customer_id;
                $trans['account_id']    = $account_id;
                $trans['type']          = 'Saving';
                $trans['created_date']  = date('Y-m-d H:i:s');
                $trans['last_modified_date'] = date('Y-m-d H:i:s');
                $trans['interest_paid'] = 0;
                $trans['fine_paid'] = 0;
                
                
                // Now add the new record to customer_installment
                $loan_query = $this->db->insert('loan_transactions', $trans);
                $transaction_id = $this->db->insert_id(); 



                // Now send the text message if the customer mobile number is provided

                if(isset($data['phone'])) { 
                    
                    $msisdn  =  $data['phone'];
                    $name = $data['name'];
                    isset($trans['amount']) ? $amount = $trans['amount'] : $amount = 0;

                    if($input['type'] == 'Saving') {
                        // Template is working with Textlocal. Do not change.
                        $msg = "Dear $name, HM Trading has registered your saving account. Initial payment of Rs $amount received. Contact for help on : +919765737487";
                    } else {
                        $msg = "Dear $name, you have been successfully registered for the HM Trading loan scheme. An amount of Rs. $amount has been given to you.";     
                    }

                    // Send message from send message service
                    $this->load->model('Sendsms_model');
                    $this->Sendsms_model->send_sms($msg, $msisdn);
                }


                $result = array();
                $result['customer_id']  = $customer_id;
                $result['account_id']   = $account_id;
                $result['transaction_id'] = $transaction_id;
                // $result['output'] = $output;
                return $result;
            }
        } catch (Exception $e) {
            log_message('error',$e->getMessage());
            $this->exceptionhandler->handle($e);
        }
    }



    function get_loan_customers($data) {
        $return_result = array();

        $offset = $data['offset'];
        $limit  = $data['limit'];
        $page   = $data['page'];

        unset($data['offset']);
        unset($data['limit']);
        unset($data['page']);

        if (isset($data['account_number'])) {
            $account_number = $data['account_number'];
            unset($data['account_number']);
        }

        // PAGINATION
        /*if (isset($account_number)) {
            $this->db->where('account_number', $account_number);
        }
        if(sizeof($data)) {
            $this->db->like($data);
        }
        $this->db->where('isActive', '1');
        $size = $this->db->count_all_results('loan_customers');

        $pagination = array();
        $pagination['size'] = $size;
        $pagination['page'] = $page;
        $pagination['offset'] = $offset;*/

        
        $this->db->select('*');
        $this->db->where('loan_customers.isActive', 1);
        $this->db->from('loan_customers');
        $this->db->where('loan_accounts.type', $data['type']);
        $this->db->where('loan_accounts.isActive', 1);
        $this->db->join('loan_accounts', 'loan_customers.customer_id = loan_accounts.customer_id');
        // $this->db->group_by('loan_customers.customer_id');
        $size = $this->db->count_all_results();

        $pagination = array();
        $pagination['size'] = $size;
        $pagination['page'] = $page;
        $pagination['offset'] = $offset;

        // SEARCH RESULT
        /*if (isset($account_number)) {
            $this->db->where('account_number', $account_number);
        }
        if(sizeof($data)) {
            $this->db->like($data);
        }
        $this->db->limit($limit, $offset);
        $this->db->where('isActive', '1');
        $this->db->order_by("customer_id", "desc");
        $query = $this->db->get('loan_customers');

        $query_result = array();
        foreach($query->result_array() as $item) {

            $this->db->where('customer_id', $item['customer_id']);
            $this->db->where('type', 'Saving');

            $item_query = $this->db->get('loan_accounts');
            $result = $item_query->row_array();
            $item['saving_account_id'] = $result['account_id'];

            $this->db->where('customer_id', $item['customer_id']);
            $this->db->where('type', 'Loan');
            $item_query = $this->db->get('loan_accounts');
            $result = $item_query->result_array();

            if(isset($result) && sizeof($result)) {
                $item['loan_account_id'] = $result[sizeof($result)-1]['account_id'];
            }

            array_push($query_result, $item);
        }*/


        $this->db->select('*');
        // $this->db->distinct('loan_customers.customer_id');
        $this->db->where('loan_customers.isActive', 1);
        $this->db->like($data);
        $this->db->from('loan_customers');
        $this->db->where('loan_accounts.type', $data['type']);
        $this->db->where('loan_accounts.isActive', 1);
        $this->db->join('loan_accounts', 'loan_customers.customer_id = loan_accounts.customer_id');
        $this->db->group_by('loan_customers.customer_id');
        $query = $this->db->get();
        $query_result = $query->result_array();
        
        $return_result['records'] = $query_result;
        $return_result['pagination'] = $pagination;
        return $return_result;
    }


    // Get customer details of the provided id
    function get_loan_customer_detail($params = array()) {
        $this->db->where('customer_id', $params['customer_id']);
        $query = $this->db->get('loan_customers');
        return $query->row_array();
    }


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


    // Soft delete of the loan customer by changing flag isActive flag from 1 to 0 
    function delete_loan_customer($params = array()) {
        // Update customer table instead of deleting the records
        $params['isActive'] = 0;
        $this->db->where('customer_id', $params['customer_id']);
        $query = $this->db->update('loan_customers', $params);

        // Also deactivate the accounts of the customer
        $this->db->where('customer_id', $params['customer_id']);
        $query = $this->db->update('loan_accounts', $params);
        return $query;
    }
}
