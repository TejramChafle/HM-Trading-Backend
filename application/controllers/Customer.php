<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Customer extends CI_Controller {

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

    public function get_customers() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Customer_model');
            $resp = $this->Customer_model->get_customers($input_data);
            echo json_encode($resp);
        }
    }


    public function add_customer() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Customer_model');
            $resp = $this->Customer_model->add_customer($input_data);
            echo json_encode($resp);
        }
        
    }


    public function get_customer_detail() {
        $input_data = $this->input->get();
        $this->load->model('Customer_model');
        $resp = $this->Customer_model->get_customer_detail($input_data);
        echo json_encode($resp);
    }

    
    public function delete_customer_detail() {
        $input_data = $this->input->get();
        $this->load->model('Customer_model');
        $resp = $this->Customer_model->delete_customer_detail($input_data);
        echo json_encode($resp);
    }



    public function add_loan_customer() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Customer_model');
            $resp = $this->Customer_model->add_loan_customer($input_data);
            echo json_encode($resp);
        }
    }


    public function get_loan_customers() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Customer_model');
            $resp = $this->Customer_model->get_loan_customers($input_data);
            echo json_encode($resp);
        }
    }


    public function get_loan_customer_detail() {
        $input_data = $this->input->get();
        $this->load->model('Customer_model');
        $resp = $this->Customer_model->get_loan_customer_detail($input_data);
        echo json_encode($resp);
    }


}
