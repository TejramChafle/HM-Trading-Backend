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


		// SEND NOTIFICATION to pending installments
	    public function send_notification_to_pending_installments() {
	        $input_data = json_decode(file_get_contents('php://input'), TRUE);
	        if ($this->input->server('REQUEST_METHOD') == 'POST') {
	            $this->load->model('Background_model');
	            $resp = $this->Background_model->send_notification_to_pending_installments($input_data);
	            echo json_encode($resp);
	        }
	    }
	}
?>
