<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Background extends CI_Controller {

	public function index()
	{
		$this->load->model('Background_model');
        $this->Background_model->send_sms();	
	}
}
