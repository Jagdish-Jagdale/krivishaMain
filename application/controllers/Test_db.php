<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_db extends CI_Controller {
    public function index() {
        echo "Testing batch size API locally...\n";
        
        // Mock POST request body
        $_POST['article_id'] = 106;
        
        // Load api model
        $this->load->model('api/Api_model');
        
        // Capture JSON output
        ob_start();
        $this->Api_model->get_article_batch_for_bundle_api();
        $output = ob_get_clean();
        
        echo "Output: \n";
        echo $output;
    }
}
