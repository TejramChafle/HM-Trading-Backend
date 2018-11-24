<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Installments extends CI_Controller {

    function __construct() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, origin, accept, access-control-allow-origin");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header('Content-Type: application/jsonp');
        // header('Content-Type: jsonp');
        parent::__construct();
    }

    public function get_installments() {

        $input_data = json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Installments_model');
            $resp = $this->Installments_model->get_installments($input_data);
            echo json_encode($resp);
        }
    }


    public function update_installment() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Installments_model');
            $resp = $this->Installments_model->update_installment($input_data);
            echo json_encode($resp);
        }
    }


    // This function will add the installment for more than one customer to their last month
    public function add_installment() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Installments_model');
            $resp = $this->Installments_model->add_installment($input_data);
            echo json_encode($resp);
        }
    }


    public function get_installments_of_selected_customers() {

        $input_data = json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Installments_model');
            $resp = $this->Installments_model->get_installments_of_selected_customers($input_data);
            echo json_encode($resp);
        }
    }


    public function lucky_draw() {

        $input_data = json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Installments_model');
            $resp = $this->Installments_model->lucky_draw($input_data);
            echo json_encode($resp);
        }
    }


    public function save_lucky_customer() {

        $input_data = json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Installments_model');
            $resp = $this->Installments_model->save_lucky_customer($input_data);
            echo json_encode($resp);
        }
    }


    public function lucky_customers() {
        $this->load->model('Installments_model');
        $resp = $this->Installments_model->lucky_customers();
        echo json_encode($resp);
    }


    public function payments() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Installments_model');
            $resp = $this->Installments_model->payments($input_data);
            echo json_encode($resp);
        }
    }



    public function get_loan_installments() {

        $input_data = json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Installments_model');
            $resp = $this->Installments_model->get_loan_installments($input_data);
            echo json_encode($resp);
        }
    }

    public function get_installment_history() {

        $input_data = json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Installments_model');
            $resp = $this->Installments_model->get_installment_history($input_data);
            echo json_encode($resp);
        }
    }


}
