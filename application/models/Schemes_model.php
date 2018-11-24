<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Schemes_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }


    // Get the list of all schemes 
    function get_schemes() {
        $query = $this->db->get('scheme');
        return $query->result_array();
    }


    // Get customer details of the provided id
    function get_scheme_detail($params = array()) {
        $this->db->where('scheme_id', $params['scheme_id']);
        $query = $this->db->get('scheme');
        // echo "<pre>";
        // print_r($query->row_array());
        // echo "</pre>";
        return $query->row_array();
    }


    // Delete customer details of the provided id
    function delete_scheme_detail($params = array()) {
        // Commenting. Since we'll not be deleting any records
        // $query = $this->db->delete('scheme', $params);
        // return $query;


        // Update scheme table
        $params['isActive'] = 0;
        $this->db->where('scheme_id', $params['scheme_id']);
        $query = $this->db->update('scheme', $params);


        // Update scheme installments
        $data = Array();
        $data['scheme_id'] = $params['scheme_id'];
        $data['isActive'] = 0;
        $this->db->where('scheme_id', $data['scheme_id']);
        $query = $this->db->update('scheme_installment', $data);
        return $query;
    }


    // Add the scheme details in db
    function add_scheme($data) {
        try {
            if(isset($data['scheme_id'])) {
                $this->db->where('scheme_id', $data['scheme_id']);
                $query = $this->db->update('scheme', $data);
                return $query;
            } else {

                
                // Inactive the rest of scheme when adding new scheme
                $params = Array();
                $params['isActive'] = '0';
                $this->db->update('scheme', $params);

                // Inactivate the rest scheme installments
                $query = $this->db->update('scheme_installment', $params);

                // Once all other schemes are inactivated, register new scheme and new scheme installments    
                $query = $this->db->insert('scheme', $data);
                $insert_id = $this->db->insert_id();

                // Now add the installments of this scheme in scheme-installment table
                // $year = date("Y");

                // decreasing for previous year
                $year = $data['year'] - 1;

                // First we need to add december month data
                $monthNum  = 12;
                $dateObj   = DateTime::createFromFormat('!m', $monthNum);
                $monthName = $dateObj->format('F');
                $time = strtotime($monthNum.'/15/'.$year);

                $input = array();
                $input['month'] = $monthName;
                $input['scheme_id'] = $insert_id;
                $input['installment_date'] = date('Y-m-d',$time);

                $input['installment_price'] = 120;
                $input['fine'] = $data['fine'];

                $query = $this->db->insert('scheme_installment', $input);

                // echo 'Printing month 12 : '.$monthName;
                // echo '<prprice
                // print_r($input);
                // echo '</pre>';

                $year++;

                // We need data for next 10 months
                for ( $i = 1; $i <= 10; $i++) { 
                    $dateObj   = DateTime::createFromFormat('!m', $i);
                    $monthName = $dateObj->format('F');
                    $time = strtotime($i.'/15/'.$year);

                    $input = array();
                    $input['month'] = $monthName;
                    $input['scheme_id'] = $insert_id;
                    $input['installment_date'] = date('Y-m-d',$time);
                    $input['fine'] = $data['fine'];

                    if ( $i <= 6 ) {
                        $input['installment_price'] = 150;
                    } else {
                        $input['installment_price'] = 200;
                    }

                    $query = $this->db->insert('scheme_installment', $input);
                } 

                return $query;
            }
        } catch (Exception $e) {
            log_message('error',$e->getMessage());
            $this->exceptionhandler->handle($e);
        }
    }



    function get_scheme_installments($params = array()) {
        $this->db->where('scheme_id', $params['scheme_id']);
        $query = $this->db->get('scheme_installment');

        $query_result = array();

        foreach($query->result_array() as $item) {

            $this->db->where('scheme_id', $item['scheme_id']);
            $item_query = $this->db->get('scheme');
            $result = $item_query->row_array();
            $item['scheme'] = $result;
            
            array_push($query_result, $item);
        }

        return $query_result;

        // echo "<pre>";
        // print_r($query->row_array());
        // echo "</pre>";
        // return $query->result_array();
    }

}
