<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {
    var $data;
    public function __construct() {
        parent::__construct();
        $valid = !(
        		empty($_SERVER['CONTENT_TYPE']) ||
        		$_SERVER['CONTENT_TYPE'] != 'application/json; charset=UTF-8' ||
        		!(isset($_SERVER['HTTP_API_KEY']) && $_SERVER['HTTP_API_KEY'] == config_item('api_key')));

        if($valid) {
        	$this->data = json_decode(file_get_contents('php://input'), TRUE);
            $valid = !!count($this->data);
        }
        if(!$valid) {
        	echo "Invalid Request";
        	exit;
        }
    }
    	
	
	public function user_signup() {
		
     	$request_fields = array('signup_mode');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->user_signup($this->data);
        }  
        echo json_encode($response);
    }
    
    public function user_login() {
		
     	$request_fields = array('user_email', 'user_password');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->user_login($this->data);
        }  
        echo json_encode($response);
    }
    
    public function user_logout() {
		
     	$request_fields = array('user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->user_logout($this->data);
        }  
        echo json_encode($response);
    }

    public function create_general_group() {
		
     	$request_fields = array('user_id', 'group_name');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->create_general_group($this->data);
        }  
        echo json_encode($response);
    }
    
    public function create_fantacy_gaming_group() {
		
     	$request_fields = array('user_id', 'group_name');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->create_fantacy_gaming_group($this->data);
        }  
        echo json_encode($response);
    }

    public function create_fantacy_sports_group() {
		
     	$request_fields = array('user_id', 'group_name');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->create_fantacy_sports_group($this->data);
        }  
        echo json_encode($response);
    }

    public function create_team_sports_group(){
     	$request_fields = array('user_id', 'group_name');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->create_team_sports_group($this->data);
        }  
        echo json_encode($response);        
    }

    public function find_free_agent_gaming(){
     	$request_fields = array('user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->find_free_agent_gaming($this->data);
        }  
        echo json_encode($response);        
    }    

    public function find_free_agent_sports(){
     	$request_fields = array('user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->find_free_agent_sports($this->data);
        }  
        echo json_encode($response);        
    }   

    public function become_free_agent_gaming(){
     	$request_fields = array('user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->become_free_agent_gaming($this->data);
        }  
        echo json_encode($response);        
    }      

     public function become_free_agent_sports(){
     	$request_fields = array('user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->become_free_agent_sports($this->data);
        }  
        echo json_encode($response);        
    }   


     public function my_groups(){
     	$request_fields = array('user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->my_groups($this->data);
        }  
        echo json_encode($response);        
    }  

     public function group_users(){
     	$request_fields = array('user_id','group_id', 'group_type');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->group_users($this->data);
        }  
        echo json_encode($response);        
    }  

    public function user_follow(){
     	$request_fields = array('user_id', 'ref_user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->user_follow($this->data);
        }  
        echo json_encode($response);        
    }      	

     public function user_contacts(){
     	$request_fields = array('user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->user_contacts($this->data);
        }  
        echo json_encode($response);        
    }      

    public function update_group_info(){
     	$request_fields = array('group_id', 'group_type');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->update_group_info($this->data);
        }  
        echo json_encode($response);        
    }  

    public function all_users(){
     	$request_fields = array('user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->all_users($this->data);
        }  
        echo json_encode($response);        
    }     

    public function add_member(){
     	$request_fields = array('user_id', 'member_id', 'group_id', 'group_type');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->add_member($this->data);
        }  
        echo json_encode($response);        
    }  

    public function remove_member(){
     	$request_fields = array('user_id', 'member_id', 'group_id', 'group_type');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->remove_member($this->data);
        }  
        echo json_encode($response);        
    }    

    public function group_mute(){
     	$request_fields = array('user_id', 'group_id', 'group_type');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->group_mute($this->data);
        }  
        echo json_encode($response);        
    }    

    public function invite_friend(){
     	$request_fields = array('user_id', 'name', 'email');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->invite_friend($this->data);
        }  
        echo json_encode($response);        
    }      

    public function add_negotiation(){
     	$request_fields = array('user_id', 'ref_user_id', 'conversation');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->add_negotiation($this->data);
        }  
        echo json_encode($response);        
    } 

    public function add_signing(){
     	$request_fields = array('user_id', 'ref_user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->add_signing($this->data);
        }  
        echo json_encode($response);        
    } 

     public function get_agency_que(){
     	$request_fields = array('user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->get_agency_que($this->data);
        }  
        echo json_encode($response);        
    }    

     public function remove_pending_signings(){
     	$request_fields = array('user_id', 'ref_user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->remove_pending_signings($this->data);
        }  
        echo json_encode($response);        
    }   

     public function remove_group(){
     	$request_fields = array('user_id', 'group_id', 'group_type');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->remove_group($this->data);
        }  
        echo json_encode($response);        
    }     

     public function follow_users(){
     	$request_fields = array('user_id');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->follow_users($this->data);
        }  
        echo json_encode($response);        
    }         

     public function assign_admin(){
     	$request_fields = array('user_id', 'group_id', 'group_type');
		$request_form_success = true;
        
		foreach ($request_fields as $request_field){		
			if (!isset($this->data[$request_field])) {
                $request_form_success = false;
                break;
			}
		}
        if (!$request_form_success) {
            $response['status'] = 0;
            $response['msg'] = config_item('msg_fill_form');
        } else {  
            $response = $this->api_model->assign_admin($this->data);
        }  
        echo json_encode($response);        
    }                  

}
