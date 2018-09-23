<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Schemes extends CI_Controller {

    function __construct() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, origin, accept, access-control-allow-origin");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header('Content-Type: application/jsonp');
        // header('Content-Type: jsonp');
        parent::__construct();
    }

    public function get_schemes() {
        $this->load->model('Schemes_model');
        $resp = $this->Schemes_model->get_schemes();
        echo json_encode($resp);
    }


    public function add_scheme() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Schemes_model');
            $resp = $this->Schemes_model->add_scheme($input_data);
            echo json_encode($resp);
        }
        
    }


    public function get_scheme_detail() {
        $input_data = $this->input->get();
        $this->load->model('Schemes_model');
        $resp = $this->Schemes_model->get_scheme_detail($input_data);
        echo json_encode($resp);
    }

    
    public function delete_scheme_detail() {
        $input_data = $this->input->get();
        $this->load->model('Schemes_model');
        $resp = $this->Schemes_model->delete_scheme_detail($input_data);
        echo json_encode($resp);
    }


    public function get_scheme_installments() {
        $input_data = $this->input->get();
        $this->load->model('Schemes_model');
        $resp = $this->Schemes_model->get_scheme_installments($input_data);
        echo json_encode($resp);
    }


}
