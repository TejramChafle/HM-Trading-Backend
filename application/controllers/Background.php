<?php
	class Background extends CI_Controller {
		public function __construct() {
	        parent::__construct();
	    }
		
		public function index() {
			$this->load->model('Background_model');
	        $this->Background_model->background_cron();	
		}

		public function background_cron() {
			$this->load->model('Background_model');
	        $this->Background_model->background_cron();	
		}
	}
?>
