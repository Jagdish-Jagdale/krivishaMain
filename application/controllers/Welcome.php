<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller { 
	public function __construct(){
		parent::__construct();
	  
	}
	public function login(){ 
		$this->form_validation->set_rules('email','email','required');
		if($this->form_validation->run() === FALSE){
			$this->load->view('admin/login');   
		}else{
			$result = $this->Admin_model->login();  

			if($result == '1'){
				$this->session->set_flashdata('success','Login successfully');
				redirect('dashboard');
			}else{
				$this->session->set_flashdata('message','Login failed, please enter valid login details');
				redirect(base_url());
			}
		}
	}
	
	public function privacy_policy() {
		$this->load->view('admin/privacy_policy');
	}
	
	public function forgot_password(){
		$this->form_validation->set_rules('email','email','required');
		if($this->form_validation->run() === FALSE){
		$this->load->view('admin/forgot_password');
		}else{

			$result = $this->Admin_model->forgot_password();  
			if($result == '1'){
				$this->session->set_flashdata('success','Password updated successfully');
				redirect(base_url());
			}else{
				$this->session->set_flashdata('message','You Have enter wrong username please try again');
				redirect('forgot_password');
			}
		     

		}

	}
	public function logout()
	{
		$this->session->unset_userdata('id');
		$this->session->unset_userdata('is_admin');
		$this->session->set_flashdata('message', 'Logged out successfully');
		redirect(base_url());
	}
       
	
}
