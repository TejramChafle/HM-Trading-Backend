<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Transactions_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
    }


    /*---------------------------------------------------------------------------------------
        : This function brings the customer transaction records from the db
    ----------------------------------------------------------------------------------------*/
    function get_loan_transactions($data) {

        $offset = $data['offset'];
        $limit  = $data['limit'];
        $page   = $data['page'];

        // Filter by receipt number/transaction id
        if (isset($data['transaction_id'])) {
            $transaction_id = $data['transaction_id'];
            unset($data['transaction_id']);
        }
        // Filter by account id
        if (isset($data['account_id'])) {
            $account_id = $data['account_id'];
            unset($data['account_id']);
        }

        unset($data['offset']);
        unset($data['limit']);
        unset($data['page']);


        // PAGINATION
        // Filter by receipt number/transaction id
        if (isset($transaction_id)) {
            $this->db->where('transaction_id', $transaction_id);
        }
        // Filter by account id
        if (isset($account_id)) {
            $this->db->where('account_id', $account_id);
        }

        if(sizeof($data) && !isset($data['customer_id']) ){
            $this->db->like($data);
        } else if(isset($data['customer_id'])) {
            $this->db->where('customer_id', $data['customer_id']);
        } 

        $this->db->order_by("created_date", "desc");
        $size = $this->db->count_all_results('loan_transactions');

        $pagination = array();
        $pagination['size'] = $size;
        $pagination['page'] = $page;
        $pagination['offset'] = $offset;



        // SEARCH RESULT
        // Filter by receipt number/transaction id
        if (isset($transaction_id)) {
            $this->db->where('transaction_id', $transaction_id);
        }
        // Filter by account id
        if (isset($account_id)) {
            $this->db->where('account_id', $account_id);
        }

        if(sizeof($data) && !isset($data['customer_id']) ){
            $this->db->like($data);
        } else if(isset($data['customer_id'])) {
            $this->db->where('customer_id', $data['customer_id']);
        } 

        /*if (isset($data['customer_id'])) {
            $this->db->order_by("account_id", "asc");    
        } else {
            $this->db->order_by("created_date", "desc");
        }*/
        $this->db->order_by("created_date", "asc");
        
        $this->db->limit($limit, $offset);
        $query = $this->db->get('loan_transactions');

        // $result['transactions'] = $query->result_array();
        $transactions = $query->result_array();     

        if (isset($data['customer_id'])) {

            $result = array();
            $this->db->where('customer_id', $data['customer_id']);
            $query = $this->db->get('loan_customers');
            $result['customer'] = $query->row_array();

            $balances = array();

            /*$this->db->where('customer_id', $data['customer_id']);
            $query = $this->db->get('loan_accounts');
            $result['account'] = $query->row_array();

            $result['account']['type'] == 'Loan' ? $previous_balance = $result['account']['amount'] : $previous_balance = 0;
            foreach ($transactions as $value) {
                $value['previous_balance'] = $previous_balance; 
                $previous_balance = $value['balance'];
                $value['total'] = $value['amount'] + $value['fine_paid'] + $value['interest_paid'];
                array_push($balances, $value);
            }*/

            
            /*foreach ($transactions as $transaction) {
                if (array_search($transaction['account_id'], $balances)) {
                    $previous_transaction = array_search($transaction['account_id'], $balances);
                    $transaction['previous_balance'] = $previous_transaction['balance'];
                } else {
                    if ($transaction['type']=='Saving') {
                        $transaction['previous_balance'] = 0;
                    } else {
                        $this->db->where('account_id', $transaction['account_id']);
                        $query = $this->db->get('loan_accounts');
                        $account = $query->row_array();
                        $transaction['previous_balance'] = $account['amount'];
                    }
                }
                 
                // $previous_balance = $value['balance'];
                $transaction['total'] = $transaction['amount'] + $transaction['fine_paid'] + $transaction['interest_paid'];
                array_push($balances, $transaction);
            }*/

            for ($i=0; $i<sizeof($transactions); $i++) {
                if (isset($transactions[$i-1]['account_id']) && $transactions[$i-1]['account_id'] == $transactions[$i]['account_id']) {
                    $transactions[$i]['previous_balance'] = $transactions[$i-1]['balance'];
                } else {
                    if ($transactions[$i]['type']=='Saving') {
                        $transactions[$i]['previous_balance'] = 0;
                    } else {
                        $this->db->where('account_id', $transactions[$i]['account_id']);
                        $query = $this->db->get('loan_accounts');
                        $account = $query->row_array();
                        $transactions[$i]['previous_balance'] = $account['amount'];
                    }
                }
                $transactions[$i]['total'] = $transactions[$i]['amount'] + $transactions[$i]['fine_paid'] + $transactions[$i]['interest_paid'];
                array_push($balances, $transactions[$i]);
            }

            $result['transactions'] = $balances;
            return $result;

        } else {
            $result = array();
            foreach($transactions as $item) {

                $this->db->where('customer_id', $item['customer_id']);
                $item_query = $this->db->get('loan_customers');
                $query_result = $item_query->row_array();
                $item['customer'] = $query_result;

                $this->db->where('customer_id', $item['customer_id']);
                $query = $this->db->get('loan_accounts');
                $item['account'] = $query->row_array();

                $this->db->where('customer_id', $item['customer_id']);
                $this->db->where('type', $item['type']);
                $query = $this->db->get('loan_transactions');
                $trans = $query->result_array();

                if(sizeof($trans) > 1) {
                    $size = sizeof($trans) - 2;
                    $previous_balance = $trans[$size]['balance'];
                    // $previous_balance = $transaction['balance'];
                } else {
                    $item['account']['type'] == 'Loan' ? $previous_balance = $item['account']['amount'] : $previous_balance = 0;
                }

                $item['previous_balance'] = $previous_balance; 
                $previous_balance = $item['balance'];
                $item['total'] = $item['amount'] + $item['interest_paid'] + $item['fine_paid'];

                array_push($result, $item);
            }

            // $params = array();
            // $data['database'] = 'loan_transactions';
            // $pagination = $this->pagination($data);
            // $pagination['page'] = $page;
            // $pagination['offset'] = $offset;

            $return_result = array();
            $return_result['records'] = $result;
            $return_result['pagination'] = $pagination;
            return $return_result;
        }
        
    }





    function get_loan_accounts($data) {

        if(sizeof($data)){
            $this->db->like($data);
        }

        $query = $this->db->get('loan_accounts');
        $query_result = array();

        foreach($query->result_array() as $item) {

            $this->db->where('customer_id', $item['customer_id']);
            $item_query = $this->db->get('loan_customers');
            $result = $item_query->row_array();
            $item['customer'] = $result;

            $installment_ids = explode(',', $item['installment_id']);

            $installments = array();

            foreach ($installment_ids as $installment_id) {
                $this->db->where('installment_id', $installment_id);
                $item_query = $this->db->get('loan_installments');
                array_push($installments, $item_query->row_array());
            }

            $item['installments'] = $installments;

            array_push($query_result, $item);
        }

        return $query_result;
    }





    // This function will add the installment for more than one customer to their last month
    function add_saving($params = array()) {
        
        $this->db->where('customer_id', $params['customer_id']);
        $query = $this->db->get('loan_customers');
        $customer = $query->row_array();

        /*$this->db->where('customer_id', $params['customer_id']);
        $this->db->where('type', $params['type']);
        $query = $this->db->get('loan_accounts');
        $account = $query->row_array();*/

        $input = array();
        $input['account_id']    = $params['account_id'];
        $input['customer_id']   = $params['customer_id'];
        $input['amount']        = $params['amount'];
        $input['balance']       = $params['balance'] + $params['amount'];
        $input['type']          = $params['type'];
        $input['interest_fine_paid']    = $params['interest_fine_paid'];
        $input['created_date']          = date('Y-m-d H:i:s');
        

        // Now add the new record to customer_installment
        $query = $this->db->insert('loan_transactions', $input);
        $insert_id = $this->db->insert_id(); 

        if(isset($customer['phone'])) {

            $msisdn =   $customer['phone'];
            $name   =   $customer['name'];
            $month  =   date('F');
            $amount =   $params['amount'];

            // Replace with your Message content
            $msg = "Dear $name, we have received saving amount of Rs $amount of the month $month.";

            // Send message from send message service
            $this->load->model('Sendsms_model');
            $this->Sendsms_model->send_sms($msg, $msisdn);
        }

        return  $insert_id;
    }




    // This function will add the installment for more than one customer to their last month
    function add_loan_installment($params = array()) {


        /*$installments   = $params['installments'];
        $account        = $params['account'];

        $installment_ids = array();

        foreach ($installments as $key => $value) {
            $data = array();
            $data['paid_date'] = date('Y-m-d H:i:s');
            $data['paid_fine'] = $value['paid_fine'];
            $data['paid_status'] = 'Paid';
            $data['paid_amount'] = $value['emi'];   

            $this->db->where('installment_id', $value['installment_id']);
            $query = $this->db->update('loan_installments', $data);

            array_push($installment_ids, $value['installment_id']);
        }*/

        // $installment_ids = explode(',', $item['installment_ids']);

        $this->db->where('customer_id', $params['customer_id']);
        $query = $this->db->get('loan_customers');
        $customer = $query->row_array();

        $input = array();
        $input['account_id']    = $params['account_id'];
        $input['customer_id']   = $params['customer_id'];
        $input['amount']        = $params['amount'];
        
        if($params['type'] == 'Loan'){
            $input['balance'] = $params['balance'] - $params['amount'];    
        } else {
            $input['balance'] = $params['balance'] + $params['amount'];
        }
        
        $input['type']          = $params['type'];
        $input['interest_paid'] = $params['interest_paid'];
        $input['fine_paid']     = $params['fine_paid'];
        $input['created_date']  = date('Y-m-d H:i:s');

        // Now add the new record to customer_installment
        $query = $this->db->insert('loan_transactions', $input);
        $insert_id = $this->db->insert_id(); 

        // If the balance for loan account is zero then, deactivate the account
        if ($input['balance']==0 && $params['type'] == 'Loan' && isset($insert_id)) {
            $data = array();
            $data['isActive'] = 0;
            $data['last_modified_date'] = date('Y-m-d H:i:s');;
            $this->db->where('account_id', $input['account_id']);
            $query = $this->db->update('loan_accounts', $data);
        }

        if(isset($customer['phone'])) {

            $msisdn  =  $customer['phone'];
            $name = $customer['name'];
            $amount = $params['amount'];
            $fine = $params['fine_paid'];
            $interest_paid = $params['interest_paid'];
            $balance = $input['balance'];

            // Replace with your Message content
            if($params['type'] == 'Loan') {
                // $msg = "Dear $name, HM Trading has received loan interest of Rs $amount.";
                $msg = "Dear $name, HM Trading has received a loan amount of Rs $amount, with interest of Rs $interest_paid.";
            } else {
                // Template is working with Textlocal. Do not change.
                $msg = "Dear $name, HM Trading has received a saving amount of Rs $amount.";
            }

            if($fine) {
                // Template is working with Textlocal. Do not change.
                $msg .= " A fine of Rs $fine has been imposed for late payment.";   
            }

            if($params['type'] == 'Loan') {
                $msg .= " The remaining balance in your loan account is Rs ".$input['balance'].'.';
            } else {
                // Template is working with Textlocal. Do not change.
                $msg .= " The total saving amount is Rs ".$input['balance'].'.';
            }

            // Template is working with Textlocal. Do not change.
            $msg .= " Contact for help on : +919765737487.";

            // Send message from send message service
            $this->load->model('Sendsms_model');
            $this->Sendsms_model->send_sms($msg, $msisdn);
        }

        return  $insert_id;
    }







    // This function will add the installment for more than one customer to their last month
    function create_loan_account($params = array()) {

        $this->db->where('customer_id', $params['customer_id']);
        $this->db->where('type', 'Loan');
        $query = $this->db->get('loan_transactions');

        $loans = json_decode(json_encode($query->last_row()), True);

        if (isset($loans) && $loans['balance']==0) {
            $insert_id = $this->save_loan_account($params);
            return  'success: '.$insert_id;
        } else if(isset($loans) && $loans['balance']>0) {
            return 'error: Balance loan amount from previous loan is Rs '.$loans['balance'];
        } else {
            $this->db->where('customer_id', $params['customer_id']);
            $this->db->where('type', 'Loan');
            $query = $this->db->get('loan_accounts');
            // $accounts = $query->row_array();
            $accounts = json_decode(json_encode($query->last_row()), True);
            if (isset($accounts)) {
                return 'error: Balance loan amount from previous loan is Rs '.$accounts['amount'];
            } else {
                $insert_id = $this->save_loan_account($params);
                return  'success: '.$insert_id;
            }
        }
    }


    // Create and Save the loan account information
    function save_loan_account($params = array()) {
        $this->db->where('customer_id', $params['customer_id']);
        $query = $this->db->get('loan_customers');
        $customer = $query->row_array();

        $input = array();
        $input['amount']        = $params['amount'];
        // $input['balance']       = $params['balance'];
        // $input['emi']           = $params['emi'];
        $input['customer_id']   = $params['customer_id'];
        $input['type']          = 'Loan';
        $input['interest_fine'] = 300;

        if(isset($params['created_date'])) {
            $input['created_date']  = $params['created_date'];
        } else {
            $input['created_date']  = date('Y-m-d H:i:s');
        }
        
        $input['last_modified_date']  = date('Y-m-d H:i:s');

        // Now add the new record to customer_installment
        $query = $this->db->insert('loan_accounts', $input);
        $insert_id = $this->db->insert_id(); 


        // $installments = array();
        // $installments = json_decode($params['installments']);

        // // Now add the new record to loan_installments
        // foreach ($installments as $value) {
        //     $value->paid_status = 'Unpaid';
        //     $value->account_id  = $insert_id;
        //     $query = $this->db->insert('loan_installments', $value);
        // }


        if(isset($customer['phone'])) {

            // Replace with client name
            $name   = $customer['name'];
            $amount = $params['amount'];
            $msisdn = $customer['phone'];

            // Replace with your Message content
            // $msg = "Dear $name, we have confirmed your loan for the amount of Rs. $amount. Contact for help: +919765737487.";

            // Template is working with Textlocal. Do not change.
            $msg = "Dear $name, HM Trading has registered your loan account. A loan amount of Rs. $amount has been given to you. Contact for help on : +919765737487";
            
            // Send message from send message service
            $this->load->model('Sendsms_model');
            $this->Sendsms_model->send_sms($msg, $msisdn);
        }

        return $insert_id;
    }



    // This function will save the loan interest given by the customer
    function save_loan_interest($params = array()) {


        $amount     = $params['interest'];
        $account_id = $params['account_id'];

        $this->db->where('account_id', $params['account_id']);
        $this->db->where('paid_status', 'Unpaid');
        $query = $this->db->get('loan_installments'); 
        $result = $query->result_array();    

        $installment_ids = array();
        $fine = 0;

        foreach ($result as $key => $value) {

            if($amount >= $value['emi']) {
                $data = array();

                if (date('Y-m-d') > $value['installment_date']) {
                    $data['paid_fine'] = 300;
                    $amount = $amount - 300;
                    $fine += 300;
                }

                $data['paid_date'] = date('Y-m-d H:i:s');
                $data['paid_status'] = 'Paid';
                $data['paid_amount'] = $value['emi'];

                $this->db->where('installment_id', $value['installment_id']);
                $query = $this->db->update('loan_installments', $data);

                $amount = $amount - $value['emi'];

                array_push($installment_ids, $value['installment_id']);
            } else {
                $data = array();
                $data['paid_status'] = 'Unpaid';
                $data['paid_amount'] = $amount;
                $data['paid_date'] = date('Y-m-d H:i:s');

                $this->db->where('installment_id', $value['installment_id']);
                $query = $this->db->update('loan_installments', $data);
                array_push($installment_ids, $value['installment_id']);

                break;
            }
            
        }

        // $installment_ids = explode(',', $item['installment_ids']);

        $input = array();
        $input['account_id']    = $params['account_id'];
        $input['customer_id']   = $params['customer_id'];
        $input['type']          = 'Loan';
        $input['amount']        = $params['interest'];
        $input['interest_fine_paid']    = $fine;
        $input['installment_id']        = implode(',', $installment_ids);
        $input['created_date']          = date('Y-m-d H:i:s');
        $input['last_modified_date']    = date('Y-m-d H:i:s');

        // Now add the new record to customer_installment
        $query = $this->db->insert('loan_transactions', $input);
        $insert_id = $this->db->insert_id(); 

        if(isset($customer['phone'])) {

            $msisdn  =  $customer['phone'];
            $name = $customer['name'];
            $month = $month = date('F');
            $amount = $params['amount'];

            // Replace with your Message content
            $msg = "Dear $name, we have received saving amount of Rs $amount of the month $month.";

            // Send message from send message service
            $this->load->model('Sendsms_model');
            $this->Sendsms_model->send_sms($msg, $msisdn);
        }

        return  $insert_id;
    }


    function reports($params = array()) {

        // Get all reports of the saving transactions

        $this->db->select('amount, SUM(amount) as saving_received');
        $this->db->select('fine_paid, SUM(fine_paid) as fine_paid');
        $this->db->select('created_date, Month(created_date) as month');

        $this->db->where('type', 'Saving'); 
        // $this->db->order_by("created_date", "desc"); 
        // $this->db->group_by(array("mag_year", "mag_month"));
        // $this->db->group_by(Month('created_date'));
        $this->db->group_by('Month(created_date)');    
        $query = $this->db->get('loan_transactions');
        $saving = $query->result();


        // Get all reports of the loan transactions
        $this->db->select('amount, SUM(amount) as installment_received');
        $this->db->select('interest_paid, SUM(interest_paid) as interest_paid');
        $this->db->select('fine_paid, SUM(fine_paid) as fine_paid');

        $this->db->select('created_date, Month(created_date) as month');
        $this->db->where('type', 'Loan'); 
        $this->db->group_by('Month(created_date)');
        $query = $this->db->get('loan_transactions');
        $loan = $query->result();


        // Get all reports of the loan transactions
        // $this->db->where('type', 'Loan'); 
        // $this->db->order_by("created_date", "desc"); 
        // $this->db->group_by(array("mag_year", "mag_month"));
        // $query = $this->db->get('loan_transactions');
        // $loan = $query->result();


        $this->db->select('amount, SUM(amount) as loan_disbursed');
        
        $this->db->select('created_date, Month(created_date) as month');
        $this->db->where('type', 'Loan'); 
        $this->db->group_by('Month(created_date)');
        $query = $this->db->get('loan_accounts');
        $loan_accounts = $query->result();

        $result = array();
        $result['loan']['loan'] = $loan;
        $result['loan']['saving'] = $saving;
        $result['loan']['disbursement'] = $loan_accounts;
        return $result;
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
        $query = $this->db->get($db);
        $rowcount = $query->num_rows();

        $result = array();
        $result['size'] = $rowcount;
        return $result;
    }


}
