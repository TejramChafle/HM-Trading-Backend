<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Transactions extends CI_Controller {

    function __construct() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, origin, accept, access-control-allow-origin");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header('Content-Type: application/jsonp');
        // header('Content-Type: jsonp');
        parent::__construct();
    }


    /*---------------------------------------------------------------------------------------
        : Functions for the hmtrading.biz
    ----------------------------------------------------------------------------------------*/

    public function get_loan_transactions() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Transactions_model');
            $resp = $this->Transactions_model->get_loan_transactions($input_data);
            echo json_encode($resp);
        }
    }


    public function get_loan_accounts() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Transactions_model');
            $resp = $this->Transactions_model->get_loan_accounts($input_data);
            echo json_encode($resp);
        }
    }


    public function add_saving() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Transactions_model');
            $resp = $this->Transactions_model->add_saving($input_data);
            echo json_encode($resp);
        }
    }


    public function add_loan_installment() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Transactions_model');
            $resp = $this->Transactions_model->add_loan_installment($input_data);
            echo json_encode($resp);
        }
    }


    public function create_loan_account() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Transactions_model');
            $resp = $this->Transactions_model->create_loan_account($input_data);
            echo json_encode($resp);
        }
    }


    public function save_loan_interest() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Transactions_model');
            $resp = $this->Transactions_model->save_loan_interest($input_data);
            echo json_encode($resp);
        }
    }


    public function reports() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Transactions_model');
            $resp = $this->Transactions_model->reports($input_data);
            echo json_encode($resp);
        }
    }


}
