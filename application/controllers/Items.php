<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Items extends CI_Controller {

    function __construct() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, origin, accept, access-control-allow-origin");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header('Content-Type: application/jsonp');
        // header('Content-Type: jsonp');
        parent::__construct();
    }

    public function get_items() {
        // header('Content-Type: application/jsonp');
        $this->load->model('Items_model');
        $resp = $this->Items_model->get_items();
        echo json_encode($resp);
    }


    public function add_item() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Items_model');
            $resp = $this->Items_model->add_item($input_data);
            echo json_encode($resp);
        }
    }


    public function get_item_detail() {
        $input_data = $this->input->get();
        $this->load->model('Items_model');
        $resp = $this->Items_model->get_item_detail($input_data);
        echo json_encode($resp);
    }

    
    public function delete_item() {
        $input_data = $this->input->get();
        $this->load->model('Items_model');
        $resp = $this->Items_model->delete_item($input_data);
        echo json_encode($resp);
    }


}
