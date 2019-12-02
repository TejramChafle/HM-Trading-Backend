<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Items_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }


    // Get the list of all items 
    function get_items($data) {
        $offset = $data['offset'];
        $limit  = $data['limit'];
        $page   = $data['page'];

        unset($data['offset']);
        unset($data['limit']);
        unset($data['page']);
        
        //PAGINATION
        $size = $this->db->count_all_results('item');
        $pagination = array();
        $pagination['size'] = $size;
        $pagination['page'] = $page;
        $pagination['offset'] = $offset;

        // SEARCH RESULT
        $this->db->limit($limit, $offset);
        $this->db->order_by("item_id", "desc");
        $query = $this->db->get('item');
            
        $return_result = array();    
        $return_result['records'] = $query->result_array();
        $return_result['pagination'] = $pagination;

        return $return_result;
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

}
