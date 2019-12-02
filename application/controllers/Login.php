<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	function __construct() {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, origin, accept, access-control-allow-origin");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header('Content-Type: application/jsonp');
        // header('Content-Type: jsonp');
        parent::__construct();
    }

    public function signIn() {
        $input_data= json_decode(file_get_contents('php://input'), TRUE);

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $this->load->model('Login_model');
            $login_details = $this->Login_model->sign_in($input_data);

            if ($login_details!=null) {
                $this->load->library('session');
                $this->session->set_userdata('login_details', $login_details);
            }
            echo json_encode($login_details);
        }
    }
    
    public function signOut() {
        $this->session->unset_userdata('login_details');
        $this->session->sess_destroy();
        ?>
       <script type="text/javascript">
           location.href='/';
       </script>
       <?php
    }
}
