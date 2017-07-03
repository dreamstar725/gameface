<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api_model extends CI_Model {

    public function __construct() {
        
    }
	
	public function user_signup($params){
		$result = array();
		$status = 0;
		$msg = '';
		if((int)$params['signup_mode'] == 1){
			$query = $this->db->get_where('gf_users', array('user_facebook_id'=>$params['user_facebook_id']));
			if($query->num_rows() > 0){
				$user_full_name = $params['user_first_name'] . ' ' . $params['user_last_name'];
				$user_data = array(
					'user_name'=>$params['user_name'],
					'user_email'=>$params['user_email'],
					'user_first_name'=>$params['user_first_name'],
					'user_last_name'=>$params['user_last_name'],
					'user_full_name'=>$user_full_name,
					'user_photo_url'=>$params['user_photo_url'],
					'user_location_latitude'=>$params['user_location_latitude'],
					'user_location_longitude'=>$params['user_location_longitude'],
					'user_closed' =>0
				);
				$this->db->update('gf_users', $user_data, array('user_facebook_id'=>$params['user_facebook_id']));
				$current_user_id = element('user_id', $query->row_array());
				$result['current_user_id'] = $current_user_id;
				$status = 1;
				$msg = 'success';
			}else{
				$user_full_name = $params['user_first_name'] . ' ' . $params['user_last_name'];
				$user_data = array(
					'user_facebook_id'=>$params['user_facebook_id'],
					'user_name'=>$params['user_name'],
					'user_email'=>$params['user_email'],
					'user_first_name'=>$params['user_first_name'],
					'user_last_name'=>$params['user_last_name'],
					'user_full_name'=>$user_full_name,
					'user_photo_url'=>$params['user_photo_url'],
					'user_location_latitude'=>$params['user_location_latitude'],
					'user_location_longitude'=>$params['user_location_longitude'],
					'user_closed' =>0
				);
				$this->db->insert('gf_users', $user_data);
				$insert_id = $this->db->insert_id();
				$this->db->insert('gf_follow', array('follow_user_id'=>$insert_id));
				$this->db->insert('gf_contact', array('contact_user_id'=>$insert_id));
				$result['current_user_id'] = $insert_id;
				$status = 1;
				$msg = 'success';				
			}
		}elseif ((int)$params['signup_mode'] == 2) {
			$query = $this->db->get_where('gf_users', array('user_email'=>$params['user_email']));
			if($query->num_rows() == 0){
					$user_full_name = $params['user_first_name'] . ' ' . $params['user_last_name'];
					$current_time = time();
					$image_path = config_item('path_media_users');
					$image_name = $current_time . '.jpg';
					$image_url = $image_path . $image_name;
					$binary = base64_decode($params['user_photo_data']);
					header('Content-Type: bitmap; charset=utf-8');
					$file = fopen($image_url, 'w');
					if($file){
						fwrite($file, $binary);				
					}else{
						$status = 2;
						$msg = 'File Upload Failed';
					}
					fclose($file);
					$photo_url = config_item('server_url') . $image_url;
					$user_data = array(
							'user_email'=>$params['user_email'],
							'user_password'=>$this->get_user_auth($params['user_password']),
							'user_first_name'=>$params['user_first_name'],
							'user_last_name'=>$params['user_last_name'],
							'user_full_name'=>$user_full_name,
							'user_photo_url'=>$photo_url,
							'user_location_latitude'=>$params['user_location_latitude'],
							'user_location_longitude'=>$params['user_location_longitude'],
							'user_closed' =>0
					);
					$this->db->insert('gf_users', $user_data);
					$insert_id = $this->db->insert_id();
					$this->db->insert('gf_follow', array('follow_user_id'=>$insert_id));
					$this->db->insert('gf_contact', array('contact_user_id'=>$insert_id));
					$result['current_user_id'] = $insert_id;
					$status = 1;
					$msg = 'success';
			}else{
					$status = 2;
					$msg = 'This email already registered.';
			}
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}
	
	public function user_login($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_email'=>$params['user_email'], 'user_password'=>$this->get_user_auth($params['user_password'])));
		if($query->num_rows() > 0){
			$this->db->update('gf_users', array('user_closed'=>0), array('user_email'=>$params['user_email']));
			$result['current_user_id'] = element('user_id', $query->row_array());
			$status = 1;
			$msg = 'success';
		}else{
			$status = 2;
			$msg = 'Invalid email or password.';
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}
	
	public function user_logout($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$this->db->update('gf_users', array('user_closed'=>1), array('user_id'=>$params['user_id']));
			$status = 1;
			$msg = 'success';
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}

	public function create_general_group($params){
		$result = array();
		$status = 0;
		$msg = '';		
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$this->db->insert('gf_general_group', array('gg_user_id'=>$params['user_id'],'gg_name'=>$params['group_name'], 'gg_member_ids'=>$params['user_id'] . ','));
			$my_groups = array();
			$general_groups = $this->db->get_where('gf_general_group', array('gg_user_id'=>$params['user_id']))->result_array();
			foreach($general_groups as $general_group){
				$group = array(
					'group_id'=>$general_group['gg_id'],
					'group_name'=>$general_group['gg_name'],
					'group_member_ids'=>$general_group['gg_member_ids'],
					'group_type'=>"1"
				);
				$my_groups[] = $group;
			}
			$gaming_groups = $this->db->get_where('gf_fantacy_gaming_group', array('fgg_user_id'=>$params['user_id']))->result_array();
			foreach($gaming_groups as $gaming_group){
				$group = array(
					'group_id'=>$gaming_group['fgg_id'],
					'group_name'=>$gaming_group['fgg_name'],
					'group_member_ids'=>$gaming_group['fgg_member_ids'],
					'group_type'=>"2"
				);
				$my_groups[] = $group;
			}
			$sports_groups = $this->db->get_where('gf_fantacy_sports_group', array('fsg_user_id'=>$params['user_id']))->result_array();
			foreach($sports_groups as $sports_group){
				$group = array(
					'group_id'=>$sports_group['fsg_id'],
					'group_name'=>$sports_group['fsg_name'],
					'group_member_ids'=>$sports_group['fsg_member_ids'],
					'group_type'=>"3"
				);
				$my_groups[] = $group;
			}
			$team_groups = $this->db->get_where('gf_team_sport_group', array('tsg_user_id'=>$params['user_id']))->result_array();
			foreach($team_groups as $team_group){
				$group = array(
					'group_id'=>$team_group['tsg_id'],
					'group_name'=>$team_group['tsg_name'],
					'group_member_ids'=>$team_group['tsg_member_ids'],
					'group_type'=>"4"
				);
				$my_groups[] = $group;
			}
			$result['my_groups'] = $my_groups;
			$status = 1;
			$msg = 'Success';						
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}

	public function create_fantacy_gaming_group($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$group_info = array(
				'fgg_user_id'=>$params['user_id'],
				'fgg_name'=>$params['group_name'],
				'fgg_member_ids'=>$params['user_id'] . ',',
				'fgg_system'=>$params['system'],
				'fgg_game'=>$params['game']
			);
			$this->db->insert('gf_fantacy_gaming_group', $group_info);
			$my_groups = array();
			$general_groups = $this->db->get_where('gf_general_group', array('gg_user_id'=>$params['user_id']))->result_array();
			foreach($general_groups as $general_group){
				$group = array(
					'group_id'=>$general_group['gg_id'],
					'group_name'=>$general_group['gg_name'],
					'group_member_ids'=>$general_group['gg_member_ids'],
					'group_type'=>"1"
				);
				$my_groups[] = $group;
			}
			$gaming_groups = $this->db->get_where('gf_fantacy_gaming_group', array('fgg_user_id'=>$params['user_id']))->result_array();
			foreach($gaming_groups as $gaming_group){
				$group = array(
					'group_id'=>$gaming_group['fgg_id'],
					'group_name'=>$gaming_group['fgg_name'],
					'group_member_ids'=>$gaming_group['fgg_member_ids'],
					'group_type'=>"2"
				);
				$my_groups[] = $group;
			}
			$sports_groups = $this->db->get_where('gf_fantacy_sports_group', array('fsg_user_id'=>$params['user_id']))->result_array();
			foreach($sports_groups as $sports_group){
				$group = array(
					'group_id'=>$sports_group['fsg_id'],
					'group_name'=>$sports_group['fsg_name'],
					'group_member_ids'=>$sports_group['fsg_member_ids'],
					'group_type'=>"3"
				);
				$my_groups[] = $group;
			}
			$team_groups = $this->db->get_where('gf_team_sport_group', array('tsg_user_id'=>$params['user_id']))->result_array();
			foreach($team_groups as $team_group){
				$group = array(
					'group_id'=>$team_group['tsg_id'],
					'group_name'=>$team_group['tsg_name'],
					'group_member_ids'=>$team_group['tsg_member_ids'],
					'group_type'=>"4"
				);
				$my_groups[] = $group;
			}
			$result['my_groups'] = $my_groups;
			$status = 1;
			$msg = 'Success';				
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;		
	}



	public function create_fantacy_sports_group($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$group_info = array(
				'fsg_user_id'=>$params['user_id'],
				'fsg_name'=>$params['group_name'],
				'fsg_member_ids'=>$params['user_id'] . ',',
				'fsg_league_type'=>$params['league_type'],
				'fsg_source'=>$params['source'],
				'fsg_buyin'=>$params['buyin'],
				'fsg_players_num'=>$params['players_num'],
				'fsg_payout'=>$params['payout'],
				'fsg_divisions'=>$params['divisions'],
				'fsg_age_range'=>$params['age_range'],
				'fsg_season_length'=>$params['season_length']
			);
			$this->db->insert('gf_fantacy_sports_group', $group_info);
			$my_groups = array();
			$general_groups = $this->db->get_where('gf_general_group', array('gg_user_id'=>$params['user_id']))->result_array();
			foreach($general_groups as $general_group){
				$group = array(
					'group_id'=>$general_group['gg_id'],
					'group_name'=>$general_group['gg_name'],
					'group_member_ids'=>$general_group['gg_member_ids'],
					'group_type'=>"1"
				);
				$my_groups[] = $group;
			}
			$gaming_groups = $this->db->get_where('gf_fantacy_gaming_group', array('fgg_user_id'=>$params['user_id']))->result_array();
			foreach($gaming_groups as $gaming_group){
				$group = array(
					'group_id'=>$gaming_group['fgg_id'],
					'group_name'=>$gaming_group['fgg_name'],
					'group_member_ids'=>$gaming_group['fgg_member_ids'],
					'group_type'=>"2"
				);
				$my_groups[] = $group;
			}
			$sports_groups = $this->db->get_where('gf_fantacy_sports_group', array('fsg_user_id'=>$params['user_id']))->result_array();
			foreach($sports_groups as $sports_group){
				$group = array(
					'group_id'=>$sports_group['fsg_id'],
					'group_name'=>$sports_group['fsg_name'],
					'group_member_ids'=>$sports_group['fsg_member_ids'],
					'group_type'=>"3"
				);
				$my_groups[] = $group;
			}
			$team_groups = $this->db->get_where('gf_team_sport_group', array('tsg_user_id'=>$params['user_id']))->result_array();
			foreach($team_groups as $team_group){
				$group = array(
					'group_id'=>$team_group['tsg_id'],
					'group_name'=>$team_group['tsg_name'],
					'group_member_ids'=>$team_group['tsg_member_ids'],
					'group_type'=>"4"
				);
				$my_groups[] = $group;
			}
			$result['my_groups'] = $my_groups;
			$status = 1;
			$msg = 'Success';				
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;			
	}



	public function create_team_sports_group($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
				$group_info = array(
					'tsg_user_id'=>$params['user_id'],
					'tsg_name'=>$params['group_name'],
					'tsg_member_ids'=>$params['user_id'] . ',',
					'tsg_sport'=>$params['sport'],
					'tsg_snack_schedule_set'=>$params['snack_schedule_set'],
					'tsg_snack_schedule'=>$params['snack_schedule']
				);
				$this->db->insert('gf_team_sport_group', $group_info);
				$my_groups = array();
				$general_groups = $this->db->get_where('gf_general_group', array('gg_user_id'=>$params['user_id']))->result_array();
			foreach($general_groups as $general_group){
				$group = array(
					'group_id'=>$general_group['gg_id'],
					'group_name'=>$general_group['gg_name'],
					'group_member_ids'=>$general_group['gg_member_ids'],
					'group_type'=>"1"
				);
				$my_groups[] = $group;
			}
			$gaming_groups = $this->db->get_where('gf_fantacy_gaming_group', array('fgg_user_id'=>$params['user_id']))->result_array();
			foreach($gaming_groups as $gaming_group){
				$group = array(
					'group_id'=>$gaming_group['fgg_id'],
					'group_name'=>$gaming_group['fgg_name'],
					'group_member_ids'=>$gaming_group['fgg_member_ids'],
					'group_type'=>"2"
				);
				$my_groups[] = $group;
			}
			$sports_groups = $this->db->get_where('gf_fantacy_sports_group', array('fsg_user_id'=>$params['user_id']))->result_array();
			foreach($sports_groups as $sports_group){
				$group = array(
					'group_id'=>$sports_group['fsg_id'],
					'group_name'=>$sports_group['fsg_name'],
					'group_member_ids'=>$sports_group['fsg_member_ids'],
					'group_type'=>"3"
				);
				$my_groups[] = $group;
			}
			$team_groups = $this->db->get_where('gf_team_sport_group', array('tsg_user_id'=>$params['user_id']))->result_array();
			foreach($team_groups as $team_group){
				$group = array(
					'group_id'=>$team_group['tsg_id'],
					'group_name'=>$team_group['tsg_name'],
					'group_member_ids'=>$team_group['tsg_member_ids'],
					'group_type'=>"4"
				);
				$my_groups[] = $group;
			}
			$result['my_groups'] = $my_groups;
			$status = 1;
			$msg = 'Success';				
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;				
	}


	public function find_free_agent_gaming($params){
		$result = array();
		$agent_result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$agent_gaming_set = array(
				'fga_system'=>$params['system'],
				'fga_game'=>$params['game']
			);
			$agent_gamings = $this->db->get_where('gf_free_gaming_agents', $agent_gaming_set)->row_array();
			$agent_member_ids = explode(',', element('fga_member_ids', $agent_gamings));
			foreach($agent_member_ids as $agent_member_id){
				if((String)$agent_member_id != ''){
					if((String)$agent_member_id == $params['user_id']) continue;
					$agent_result[] = $this->db->get_where('gf_users', array('user_id'=>$agent_member_id))->row_array();
				}
			}
			$result['agent_result'] = $agent_result;
			$status = 1;
			$msg = 'success';
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}



	public function find_free_agent_sports($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$agent_sports_set = array(
				'fsa_league_type'=>$params['league_type'],
				'fsa_source'=>$params['source'],
				'fsa_paid'=>$params['paid'],
				'fsa_buyin'=>$params['buyin'],
				'fsa_payout'=>$params['payout'],
				'fsa_divisions'=>$params['divisions']
			);
			$agent_sports = $this->db->get_where('gf_free_sports_agents', $agent_sports_set)->row_array();
			$agent_member_ids = explode(',', element('fsa_member_ids', $agent_sports));
			$agent_result = array();
			foreach($agent_member_ids as $agent_member_id){
				if((String)$agent_member_id != ''){
					if((String)$agent_member_id == $params['user_id']) continue;
					$agent_result[] = $this->db->get_where('gf_users', array('user_id'=>$agent_member_id))->row_array();
				}
			}
			$result['agent_result'] = $agent_result;
			$status = 1;
			$msg = 'success';
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	
	}


	public function become_free_agent_gaming($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$agent_info = array(
				'fga_system'=>$params['system'],
				'fga_game'=>$params['game']
			 );
			 $gaming_agent = $this->db->get_where('gf_free_gaming_agents', $agent_info);
			 if($gaming_agent->num_rows() > 0){
			 	$member_ids = element('fga_member_ids', $gaming_agent->row_array());
			 	$is_exist = false;
			 	foreach(explode(',', $member_ids) as $member_id){
				 	if($member_id == $params['user_id']){
					 	$is_exist = true;
					 	break;
				 	}
			 	}
			 	if(!$is_exist){
					$member_ids .= $params['user_id'] . ',';
					$this->db->update('gf_free_gaming_agents', array('fga_member_ids'=>$member_ids), $agent_info);
					$status = 1;
					$msg = 'success';
			 	}else{
					 $status = 2;
					 $msg = 'User already exist';
				 }
			 }else{
				 $agent_info['fga_member_ids'] = $params['user_id'] . ',';
				 $this->db->insert('gf_free_gaming_agents', $agent_info);
				 $status = 1;
				 $msg = 'success';
			 }
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}


	public function become_free_agent_sports($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$agent_info = array(
				'fsa_league_type'=>$params['league_type'],
				'fsa_source'=>$params['source'],
				'fsa_paid'=>$params['paid'],
				'fsa_payout'=>$params['payout'],
				'fsa_buyin'=>$params['buyin'],
				'fsa_divisions'=>$params['divisions'],
				'fsa_age_range'=>$params['age_range']
			);
			$sport_agent = $this->db->get_where('gf_free_sports_agents', $agent_info);
			if($sport_agent->num_rows() > 0){
				$member_ids = element('fsa_member_ids', $sport_agent->row_array());
				$is_exist = false;
				foreach(explode(',', $member_ids) as $member_id){
					if($member_id == $params['user_id']){
						$is_exist = true;
						break;
					}
				}
				if(!$is_exist){
					$member_ids .= $params['user_id'] . ',';
					$this->db->update('gf_free_sports_agents', array('fsa_member_ids'=>$member_ids), $agent_info);
					$status = 1;
					$msg = 'success';
				}else{
					$status = 2;
					$msg = 'User already exist';
				}
			}else{
				$agent_info['fsa_member_ids'] = $params['user_id'] . ',';
				$this->db->insert('gf_free_sports_agents', $agent_info);
				$status = 1;
				$msg = 'success';
			}		
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}

	
	public function my_groups($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$my_groups = array();
			$general_groups = $this->db->get_where('gf_general_group', array('gg_user_id'=>$params['user_id']))->result_array();
			foreach($general_groups as $general_group){
				$groups = array(
					'group_id'=>$general_group['gg_id'],
					'group_name'=>$general_group['gg_name'],
					'group_photo_url'=>(String)$general_group['gg_photo_url'],
					'group_member_ids'=>$general_group['gg_member_ids'],
					'group_type'=>1,
					'group_mute'=>$general_group['gg_mute']
				);
				$my_groups[] = $groups;
			}
			$gaming_groups = $this->db->get_where('gf_fantacy_gaming_group', array('fgg_user_id'=>$params['user_id']))->result_array();
			foreach($gaming_groups as $gaming_group){
				$groups = array(
					'group_id'=>$gaming_group['fgg_id'],
					'group_name'=>$gaming_group['fgg_name'],
					'group_photo_url'=>(String)$gaming_group['fgg_photo_url'],
					'group_member_ids'=>$gaming_group['fgg_member_ids'],
					'group_type'=>2,
					'group_mute'=>$gaming_group['fgg_mute']
				);
				$my_groups[] = $groups;
			}
			$sport_groups = $this->db->get_where('gf_fantacy_sports_group', array('fsg_user_id'=>$params['user_id']))->result_array();
			foreach($sport_groups as $sport_group){
				$groups = array(
					'group_id'=>$sport_group['fsg_id'],
					'group_name'=>$sport_group['fsg_name'],
					'group_photo_url'=>(String)$sport_group['fsg_photo_url'],
					'group_member_ids'=>$sport_group['fsg_member_ids'],
					'group_type'=>3,
					'group_mute'=>$sport_group['fsg_mute'],
					'season_length'=>$sport_group['fsg_season_length']
				);
				$my_groups[] = $groups;
			}
			$team_groups = $this->db->get_where('gf_team_sport_group', array('tsg_user_id'=>$params['user_id']))->result_array();
			foreach($team_groups as $team_group){
				$groups = array(
					'group_id'=>$team_group['tsg_id'],
					'group_name'=>$team_group['tsg_name'],
					'group_photo_url'=>(String)$team_group['tsg_photo_url'],
					'group_member_ids'=>$team_group['tsg_member_ids'],
					'group_type'=>4,
					'group_mute'=>$team_group['tsg_mute'],
					'game_schedule'=>$team_group['tsg_snack_schedule'],
					'snack_schedule_set'=>$team_group['tsg_snack_schedule_set']
				);
				$my_groups[] = $groups;
			}
			$result['my_groups'] = $my_groups;
			$status = 1;
			$msg = 'success';
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}


	public function group_users($params){
		$result = array();
		$group_users = array();
		$status = 0;
		$msg = '';
		if((int)$params['group_type'] == 1){
			$group = $this->db->get_where('gf_general_group', array('gg_id'=>$params['group_id']));
			if($group->num_rows() > 0){
				$user_ids = $this->remove_characters($params['user_id'], element('gg_member_ids', $group->row_array()));
				if($user_ids != ''){
				$member_ids = explode(',', $user_ids);
					foreach($member_ids as $member_id){
						if((String)$member_id != ''){
							$user = $this->db->get_where('gf_users', array('user_id'=>$member_id))->row_array();
							$follow_ref_ids = element('follow_ref_user_ids', $this->db->get_where('gf_follow', array('follow_user_id'=>$params['user_id']))->row_array());
							$is_exist = false;
							foreach(explode(',', $follow_ref_ids) as $follow_ref_id){
								if($follow_ref_id == $member_id){
									$is_exist = true;
									break;
								}
							}
							if($is_exist) $user['follow_status'] = 1;
							else $user['follow_status'] = 0;
							$group_users[] = $user;
						}
					}
				}
				$result['group_users'] = $group_users;
				$status = 1;
				$msg = 'success';
			}
		}
		elseif((int)$params['group_type'] == 2){
			$group = $this->db->get_where('gf_fantacy_gaming_group', array('fgg_id'=>$params['group_id']));
			if($group->num_rows() > 0){
				$user_ids = $this->remove_characters($params['user_id'], element('fgg_member_ids', $group->row_array()));
				if($user_ids != ''){
					$member_ids = explode(',', $user_ids);
					foreach($member_ids as $member_id){
						if((String)$member_id != ''){
							$user = $this->db->get_where('gf_users', array('user_id'=>$member_id))->row_array();
							$follow_ref_ids = element('follow_ref_user_ids', $this->db->get_where('gf_follow', array('follow_user_id'=>$params['user_id']))->row_array());
							$is_exist = false;
							foreach(explode(',', $follow_ref_ids) as $follow_ref_id){
								if($follow_ref_id == $member_id){
									$is_exist = true;
									break;
								}
							}
							if($is_exist) $user['follow_status'] = 1;
							else $user['follow_status'] = 0;
							$group_users[] = $user;
						}
					}
				}
				$result['group_users'] = $group_users;
				$status = 1;
				$msg = 'success';
			}
		}
		elseif((int)$params['group_type'] == 3){
			$group = $this->db->get_where('gf_fantacy_sports_group', array('fsg_id'=>$params['group_id']));
			if($group->num_rows() > 0){
				$user_ids = $this->remove_characters($params['user_id'], element('fsg_member_ids', $group->row_array()));
				if($user_ids != ''){
					$member_ids = explode(',', $user_ids);
					foreach($member_ids as $member_id){
						if((String)$member_id != ''){
							$user = $this->db->get_where('gf_users', array('user_id'=>$member_id))->row_array();
							$follow_ref_ids = element('follow_ref_user_ids', $this->db->get_where('gf_follow', array('follow_user_id'=>$params['user_id']))->row_array());
							$is_exist = false;
							foreach(explode(',', $follow_ref_ids) as $follow_ref_id){
								if($follow_ref_id == $member_id){
									$is_exist = true;
									break;
								}
							}
							if($is_exist) $user['follow_status'] = 1;
							else $user['follow_status'] = 0;
							$group_users[] = $user;
						}
					}
				}
				$result['group_users'] = $group_users;
				$status = 1;
				$msg = 'success';
			}
		}
		elseif((int)$params['group_type'] == 4){
			$group = $this->db->get_where('gf_team_sport_group', array('tsg_id'=>$params['group_id']));
			if($group->num_rows() > 0){
				$user_ids = $this->remove_characters($params['user_id'], element('tsg_member_ids', $group->row_array()));
				if($user_ids != ''){
					$member_ids = explode(',', $user_ids);
					foreach($member_ids as $member_id){
						if((String)$member_id != ''){
							$user = $this->db->get_where('gf_users', array('user_id'=>$member_id))->row_array();
							$follow_ref_ids = element('follow_ref_user_ids', $this->db->get_where('gf_follow', array('follow_user_id'=>$params['user_id']))->row_array());
							$is_exist = false;
							foreach(explode(',', $follow_ref_ids) as $follow_ref_id){
								if($follow_ref_id == $member_id){
									$is_exist = true;
									break;
								}
							}
							if($is_exist) $user['follow_status'] = 1;
							else $user['follow_status'] = 0;
							$group_users[] = $user;
						}
					}
				}
				$result['group_users'] = $group_users;
				$status = 1;
				$msg = 'success';
			}
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}


	public function user_follow($params){
		$result = array();
		$status = 0;
		$msg = '';
		if($params['user_id'] == $params['ref_user_id']){
			$status = 2;
			$msg = 'You can not follow yourself';
		}else{
			$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
			if($query->num_rows() > 0){
				$follow = $this->db->get_where('gf_follow', array('follow_user_id'=>$params['user_id']))->row_array();
				$ref_user_ids = (String)element('follow_ref_user_ids', $follow);
				$is_exist = false;
				foreach(explode(',', $ref_user_ids) as $ref_user_id){
					if($ref_user_id == $params['ref_user_id']){
						$is_exist = true;
						break;
					}
				}
				if($is_exist){
					$ref_user_ids = $this->remove_characters($params['ref_user_id'], $ref_user_ids);
					$follow_count = (int)element('user_follow_count', $this->db->get_where('gf_users', array('user_id'=>$params['ref_user_id']))->row_array()) - 1;
					$this->db->update('gf_users', array('user_follow_count'=>$follow_count), array('user_id'=>$params['ref_user_id']));
					$this->db->update('gf_follow', array('follow_ref_user_ids'=>$ref_user_ids), array('follow_user_id'=>$params['user_id']));
					$result['follow_count'] = $follow_count;
					$result['follow_type'] = 0;
					$status = 1;
					$msg = 'success';
				}else{
					$ref_user_ids .= $params['ref_user_id'] . ',';
					$follow_count = (int)element('user_follow_count', $this->db->get_where('gf_users', array('user_id'=>$params['ref_user_id']))->row_array()) + 1;
					$this->db->update('gf_users', array('user_follow_count'=>$follow_count), array('user_id'=>$params['ref_user_id']));
					$this->db->update('gf_follow', array('follow_ref_user_ids'=>$ref_user_ids), array('follow_user_id'=>$params['user_id']));
					$result['follow_count'] = $follow_count;
					$result['follow_type'] = 1;
					$status = 1;
					$msg = 'success';
				}
			}
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;

	}


	public function user_contacts($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$contact = $this->db->get_where('gf_contact', array('contact_user_id'=>$params['user_id']))->row_array();
			$contact_users = array();
			$ref_user_ids = (String)element('contact_ref_user_ids', $contact);
			if($ref_user_ids != ''){
				foreach(explode(',', $ref_user_ids) as $ref_user_id){
					if((String)$ref_user_id != '')
						$contact_users[] = $this->db->get_where('gf_users', array('user_id'=>$ref_user_id))->row_array();
				}
				$result['contact_users'] = $contact_users;
				$status = 1;
				$msg = 'success';
			}else{
				$status = 2;
				$msg = 'No contact users';
			}
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}

	public function update_group_info($params){
		$result = array();
		$status = 0;
		$msg = '';
		if(isset($params['group_photo_data'])){
			$current_time = time();
			$image_path = config_item('path_media_groups');
			$image_name = $current_time . $params['group_id'] . '.jpg';
			$image_url = $image_path . $image_name;
			$binary = base64_decode($params['group_photo_data']);
			header('Content-Type: bitmap; charset=utf-8');
			$file = fopen($image_url, 'w');
			if($file){
				fwrite($file, $binary);				
			}else{
				$status = 2;
				$msg = 'File Upload Failed';
			}
			fclose($file);
			$photo_url = config_item('server_url') . $image_url;
			if((int)$params['group_type'] == 1){
				$this->db->update('gf_general_group', array('gg_photo_url'=>$photo_url), array('gg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 2){
				$this->db->update('gf_fantacy_gaming_group', array('fgg_photo_url'=>$photo_url), array('fgg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';			
			}
			elseif((int)$params['group_type'] == 3){
				$this->db->update('gf_fantacy_sports_group', array('fsg_photo_url'=>$photo_url), array('fsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';			
			}
			elseif((int)$params['group_type'] == 4){
				$this->db->update('gf_fantacy_gaming_group', array('tsg_photo_url'=>$photo_url), array('tsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';			
			}
		}
		if(isset($params['group_name'])){
			if((int)$params['group_type'] == 1){
				$this->db->update('gf_general_group', array('gg_name'=>$params['group_name']), array('gg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 2){
				$this->db->update('gf_fantacy_gaming_group', array('fgg_name'=>$params['group_name']), array('fgg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';			
			}
			elseif((int)$params['group_type'] == 3){
				$this->db->update('gf_fantacy_sports_group', array('fsg_name'=>$params['group_name']), array('fsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';			
			}
			elseif((int)$params['group_type'] == 4){
				$this->db->update('gf_fantacy_gaming_group', array('tsg_name'=>$params['group_name']), array('tsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';			
			}
		}		
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;				
	}


	public function all_users($params){
		$result = array();
		$all_users = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id !='=>$params['user_id']));
		if($query->num_rows() > 0){
			$users = $query->result_array();
			foreach($users as $user){
				if((int)$params['group_type'] == 1){
					$group = $this->db->get_where('gf_general_group', array('gg_id'=>$params['group_id']));
					if($group->num_rows() > 0){
						$user_ids = $this->remove_characters($params['user_id'], element('gg_member_ids', $group->row_array()));
						if($user_ids != ''){
							$member_ids = explode(',', $user_ids);
							$is_exist = false;
							foreach($member_ids as $member_id){
								if($member_id == $user['user_id']){	
									$is_exist = true;
									break;							
								}
							}
							if(!$is_exist){
								$all_users[] = $user;
							}
						}else{
							$all_users[] = $user;
						}
					}
				}
				elseif((int)$params['group_type'] == 2){
					$group = $this->db->get_where('gf_fantacy_gaming_group', array('fgg_id'=>$params['group_id']));
					if($group->num_rows() > 0){
						$user_ids = $this->remove_characters($params['user_id'], element('fgg_member_ids', $group->row_array()));
						if($user_ids != ''){
							$member_ids = explode(',', $user_ids);
							$is_exist = false;
							foreach($member_ids as $member_id){
								if($member_id == $user['user_id']){
									$is_exist = true;
									break;
								}
							}
							if(!$is_exist){
								$all_users[] = $user;
							}
						}else{
							$all_users[] = $user;
						}
					}
				}
				elseif((int)$params['group_type'] == 3){
					$group = $this->db->get_where('gf_fantacy_sports_group', array('fsg_id'=>$params['group_id']));
					if($group->num_rows() > 0){
						$user_ids = $this->remove_characters($params['user_id'], element('fsg_member_ids', $group->row_array()));
						if($user_ids != ''){
							$member_ids = explode(',', $user_ids);
							$is_exist = false;
							foreach($member_ids as $member_id){
								if($member_id == $user['user_id']){
									$is_exist = true;
									break;
								}
							}
							if(!$is_exist){
								$all_users[] = $user;
							}
						}else{
							$all_users[] = $user;
						}
					}
				}
				elseif((int)$params['group_type'] == 4){
					$group = $this->db->get_where('gf_team_sport_group', array('tsg_id'=>$params['group_id']));
					if($group->num_rows() > 0){
						$user_ids = $this->remove_characters($params['user_id'], element('tsg_member_ids', $group->row_array()));
						if($user_ids != ''){
							$member_ids = explode(',', $user_ids);
							$is_exist = false;
							foreach($member_ids as $member_id){
								if($member_id == $user['user_id']){
									$is_exist = true;
									break;
								}
							}
							if(!$is_exist){
								$all_users[] = $user;
							}
						}else{
							$all_users[] = $user;
						}
					}
				}
			}
			$result['all_users'] = $all_users;
			$status = 1;
			$msg = 'success';
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;	
	}


	public function add_member($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			if((int)$params['group_type'] == 1){
				$member_ids = (String)element('gg_member_ids', $this->db->get_where('gf_general_group', array('gg_id'=>$params['group_id']))->row_array());
				$member_ids .= $params['member_id'] . ',';
				$this->db->update('gf_general_group', array('gg_member_ids'=>$member_ids), array('gg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 2){
				$member_ids = (String)element('fgg_member_ids', $this->db->get_where('gf_fantacy_gaming_group', array('fgg_id'=>$params['group_id']))->row_array());
				$member_ids .= $params['member_id'] . ',';
				$this->db->update('gf_fantacy_gaming_group', array('fgg_member_ids'=>$member_ids), array('fgg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 3){
				$member_ids = (String)element('fsg_member_ids', $this->db->get_where('gf_fantacy_sports_group', array('fsg_id'=>$params['group_id']))->row_array());
				$member_ids .= $params['member_id'] . ',';
				$this->db->update('gf_fantacy_sports_group', array('fsg_member_ids'=>$member_ids), array('fsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 4){
				$member_ids = (String)element('tsg_member_ids', $this->db->get_where('gf_team_sport_group', array('tsg_id'=>$params['group_id']))->row_array());
				$member_ids .= $params['member_id'] . ',';
				$this->db->update('gf_team_sport_group', array('tsg_member_ids'=>$member_ids), array('tsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}

	public function remove_member($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			if((int)$params['group_type'] == 1){
				$member_ids = (String)element('gg_member_ids', $this->db->get_where('gf_general_group', array('gg_id'=>$params['group_id']))->row_array());
				$member_ids = $this->remove_characters($params['member_id'], $member_ids);
				$this->db->update('gf_general_group', array('gg_member_ids'=>$member_ids), array('gg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 2){
				$member_ids = (String)element('fgg_member_ids', $this->db->get_where('gf_fantacy_gaming_group', array('fgg_id'=>$params['group_id']))->row_array());
				$member_ids = $this->remove_characters($params['member_id'], $member_ids);
				$this->db->update('gf_fantacy_gaming_group', array('fgg_member_ids'=>$member_ids), array('fgg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 3){
				$member_ids = (String)element('fsg_member_ids', $this->db->get_where('gf_fantacy_sports_group', array('fsg_id'=>$params['group_id']))->row_array());
				$member_ids = $this->remove_characters($params['member_id'], $member_ids);
				$this->db->update('gf_fantacy_sports_group', array('fsg_member_ids'=>$member_ids), array('fsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 4){
				$member_ids = (String)element('tsg_member_ids', $this->db->get_where('gf_team_sport_group', array('tsg_id'=>$params['group_id']))->row_array());
				$member_ids = $this->remove_characters($params['member_id'], $member_ids);
				$this->db->update('gf_team_sport_group', array('tsg_member_ids'=>$member_ids), array('tsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}	

	public function group_mute($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			if((int)$params['group_type'] == 1){
				$mute = element('gg_mute', $this->db->get_where('gf_general_group', array('gg_id'=>$params['group_id']))->row_array());
				if((int)$mute == 0) $mute = 1;
				else $mute = 0;
				$this->db->update('gf_general_group', array('gg_mute'=>$mute), array('gg_id'=>$params['group_id']));
				$result['mute_status'] = $mute;
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 2){
				$mute = element('fgg_mute', $this->db->get_where('gf_fantacy_gaming_group', array('fgg_id'=>$params['group_id']))->row_array());
				if((int)$mute == 0) $mute = 1;
				else $mute = 0;
				$this->db->update('gf_fantacy_gaming_group', array('fgg_mute'=>$mute), array('fgg_id'=>$params['group_id']));
				$result['mute_status'] = $mute;
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$parmas['group_type'] == 3){
				$mute = element('fsg_mute', $this->db->get_where('gf_fantacy_sports_group', array('fsg_id'=>$params['group_id']))->row_array());
				if((int)$mute == 0) $mute = 1;
				else $mute = 0;
				$this->db->update('gf_fantacy_sports_group', array('fsg_mute'=>$mute), array('fsg_id'=>$params['group_id']));
				$result['mute_status'] = $mute;
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$parmas['group_type'] == 4){
				$mute = element('tsg_mute', $this->db->get_where('gf_team_sport_group', array('tsg_id'=>$params['group_id']))->row_array());
				if((int)$mute == 0) $mute = 1;
				else $mute = 0;
				$this->db->update('gf_team_sport_group', array('tsg_mute'=>$mute), array('tsg_id'=>$params['group_id']));
				$result['mute_status'] = $mute;
				$status = 1;
				$msg = 'success';
			}			
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;		
	}


	public function invite_friend($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
					// Send Mail
			$to = $params['email'];
			
			$subject = 'GameFace - Invite New Friend';
			
			$message = 'Hello, ' . $params['name'] . '!<br>' . 
			element('user_full_name', $query->row_array()) . ' personally invite you in Gameface App!<br>
			<br>
			You can accept or decline this invite.<br>
			<br>
			';
			
			// Always set content-type when sending HTML email
			$headers = "MIME-Version: 1.0" . "\r\n";
			$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
			
			// More headers
			$headers .= 'From: <no-reply@thegamefaceapp.com>' . "\r\n";
			//$headers .= 'Cc: cc@example.com' . "\r\n";
			
			if(mail($to, $subject, $message, $headers)) {
				$status = 1;
			} else {
				$status = 3;
				$msg = 'An error occurred while sending invite.';
			}	
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;

	}

	public function add_negotiation($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_negotiations', array('nego_user_id'=>$params['user_id'], 'nego_ref_user_id'=>$params['ref_user_id']));
		if($query->num_rows() > 0){
			$this->db->update('gf_negotiations', array('nego_conversation'=>$params['conversation']), array('nego_user_id'=>$params['user_id'], 'nego_ref_user_id'=>$params['ref_user_id']));
			$status = 1;
			$msg = 'success';
		}else{
			$this->db->insert('gf_negotiations', array('nego_user_id'=>$params['user_id'], 'nego_ref_user_id'=>$params['ref_user_id'], 'nego_conversation'=>$params['conversation']));
			$status = 1;
			$msg = 'success';
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}

	public function add_signing($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_pending_signings', array('ps_user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$ref_user_ids = (String)element('ps_ref_user_ids', $query->row_array());
			if($ref_user_ids != ''){
				$is_exist = false;
				foreach(explode(',', $ref_user_ids) as $ref_user_id){
					if($ref_user_id == $params['ref_user_id']){
						$is_exist = true;
						break;
					}
				}
				if(!$is_exist){
					$ref_user_ids .= $params['ref_user_id'] . ',';
					$this->db->update('gf_pending_signings', array('ps_ref_user_ids'=>$ref_user_ids), array('ps_user_id'=>$params['user_id']));
					$offers = $this->db->get_where('gf_offers', array('offer_user_id'=>$params['ref_user_id']));
					if($offers->num_rows() > 0){
						$offer_ref_ids = element('offer_ref_user_ids', $offers->row_array()) . $params['user_id'] . ',';						
						$this->db->update('gf_offers', array('offer_ref_user_ids'=>$offer_ref_ids), array('offer_user_id'=>$params['ref_user_id']));
					}else{
						$this->db->insert('gf_offers', array('offer_user_id'=>$params['ref_user_id'], 'offer_ref_user_ids'=>$params['user_id'] . ','));
					}
					$status = 1;
					$msg = 'success';
				}else{
					$status = 2;
					$msg = 'Already Signed';
				}
			}else{
				$this->db->update('gf_pending_signings', array('ps_ref_user_ids'=>$params['ref_user_id'] . ','), array('ps_user_id'=>$params['user_id']));
				$offers = $this->db->get_where('gf_offers', array('offer_user_id'=>$params['ref_user_id']));
				if($offers->num_rows() > 0){
					$offer_ref_ids = element('offer_ref_user_ids', $offers->row_array()) . $params['user_id'] . ',';						
					$this->db->update('gf_offers', array('offer_ref_user_ids'=>$offer_ref_ids), array('offer_user_id'=>$params['ref_user_id']));
				}else{
					$this->db->insert('gf_offers', array('offer_user_id'=>$params['ref_user_id'], 'offer_ref_user_ids'=>$params['user_id'] . ','));
				}
				$status = 1;
				$msg = 'success';				
			}
		}else{
			$this->db->insert('gf_pending_signings', array('ps_user_id'=>$params['user_id'], 'ps_ref_user_ids'=>$params['ref_user_id'] . ','));
			$offers = $this->db->get_where('gf_offers', array('offer_user_id'=>$params['ref_user_id']));
			if($offers->num_rows() > 0){
				$offer_ref_ids = element('offer_ref_user_ids', $offers->row_array()) . $params['user_id'] . ',';						
				$this->db->update('gf_offers', array('offer_ref_user_ids'=>$offer_ref_ids), array('offer_user_id'=>$params['ref_user_id']));
			}else{
				$this->db->insert('gf_offers', array('offer_user_id'=>$params['ref_user_id'], 'offer_ref_user_ids'=>$params['user_id'] . ','));
			}
			$status = 1;
			$msg = 'success';			
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}

	public function get_agency_que($params){
		$reuslt = array();
		$user_negotiations = array();
		$user_pending_signings = array();
		$user_offers = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$negotiations = $this->db->get_where('gf_negotiations', array('nego_user_id'=>$params['user_id']));
			if($negotiations->num_rows() > 0){
				foreach($negotiations->result_array() as $negotiation){
					$user_negotiations[] = array(
						'user_id'=>$negotiation['nego_ref_user_id'],
						'user_photo_url'=>element('user_photo_url', $this->db->get_where('gf_users', array('user_id'=>$negotiation['nego_ref_user_id']))->row_array()),
						'user_name'=>element('user_full_name', $this->db->get_where('gf_users', array('user_id'=>$negotiation['nego_ref_user_id']))->row_array()),
						'user_conversation'=>$negotiation['nego_conversation']
					);
				}
			}
			$result['negotiations'] = $user_negotiations;
			$pending_signings = $this->db->get_where('gf_pending_signings', array('ps_user_id'=>$params['user_id']));
			if($pending_signings->num_rows() > 0){
				$ref_ids = explode(',', element('ps_ref_user_ids', $pending_signings->row_array()));
				foreach($ref_ids as $ref_id){
					if($ref_id != ''){
						$user_pending_signings[] = array(
							'user_id'=>$ref_id,
							'user_photo_url'=>element('user_photo_url', $this->db->get_where('gf_users', array('user_id'=>$ref_id))->row_array()),
							'user_name'=>element('user_full_name', $this->db->get_where('gf_users', array('user_id'=>$ref_id))->row_array())
						);						
					}
				}
			}
			$result['pending_signings'] = $user_pending_signings;
			$offers = $this->db->get_where('gf_offers', array('offer_user_id'=>$params['user_id']));
			if($offers->num_rows() > 0){
				$ref_ids = explode(',', element('offer_ref_user_ids', $offers->row_array()));
				foreach($ref_ids as $ref_id){
					if($ref_id != ''){
						$user_offers[] = array(
							'user_id'=>$ref_id,
							'user_photo_url'=>element('user_photo_url', $this->db->get_where('gf_users', array('user_id'=>$ref_id))->row_array()),
							'user_name'=>element('user_full_name', $this->db->get_where('gf_users', array('user_id'=>$ref_id))->row_array())
						);						
					}
				}
			}
			$result['offers'] = $user_offers;
			$status = 1;
			$msg = 'success';
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}

	public function remove_pending_signings($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_pending_signings', array('ps_user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$ref_user_ids = element('ps_ref_user_ids', $query->row_array());
			$is_exist = false;
			foreach(explode(',', $ref_user_ids) as $ref_user_id){
				if($ref_user_id == $params['ref_user_id']){
					$is_exist = true;
					break;
				}
			}
			if($is_exist){
				$ref_user_ids = $this->remove_characters($params['ref_user_id'], $ref_user_ids);
				$this->db->update('gf_pending_signings', array('ps_ref_user_ids'=>$ref_user_ids), array('ps_user_id'=>$params['user_id']));
				$status = 1;
				$msg = 'success';
			}else{
				$status = 2;
				$msg = 'This user not exist';
			}
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}

	public function remove_group($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			if((int)$params['group_type'] == 1){
				$this->db->delete('gf_general_group', array('gg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 2){
				$this->db->delete('gf_fantacy_gaming_group', array('fgg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 3){
				$this->db->delete('gf_fantacy_sports_group', array('fsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 4){
				$this->db->delete('gf_team_sport_group', array('tsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}			
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}

	public function follow_users($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			$follows = $this->db->get_where('gf_follow')->result_array();
			$follow_users = array();
			foreach($follows as $follow){
				$ref_user_ids = explode(',', element('follow_ref_user_ids', $follow));
				foreach($ref_user_ids as $ref_user_id){
					if($ref_user_id == $params['user_id']){
						$user_id = element('follow_user_id', $follow);
						$follow_users[] = array(
							'user_photo_url'=>element('user_photo_url', $this->db->get_where('gf_users', array('user_id'=>$user_id))->row_array()), 
							'user_name'=>element('user_full_name', $this->db->get_where('gf_users', array('user_id'=>$user_id))->row_array())
						);
						break;
					}
				}
			}
			$result['follow_users'] = $follow_users;
			$status = 1;
			$msg = 'success';
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}

	public function assign_admin($params){
		$result = array();
		$status = 0;
		$msg = '';
		$query = $this->db->get_where('gf_users', array('user_id'=>$params['user_id']));
		if($query->num_rows() > 0){
			if((int)$params['group_type'] == 1){
				$this->db->update('gf_general_group', array('gg_admin_id'=>$params['user_id']), array('gg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 2){
				$this->db->update('gf_fantacy_gaming_group', array('fgg_admin_id'=>$params['user_id']), array('fgg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 3){
				$this->db->update('gf_fantacy_sports_group', array('fsg_admin_id'=>$params['user_id']), array('fsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}
			elseif((int)$params['group_type'] == 4){
				$this->db->update('gf_team_sport_group', array('tsg_admin_id'=>$params['user_id']), array('tsg_id'=>$params['group_id']));
				$status = 1;
				$msg = 'success';
			}						
		}
		$result['status'] = $status;
		$result['msg'] = $msg;
		return $result;
	}



	///// Utility Function ////////////////
	
	public function send_APNS($apns_id_receiver, $apns_id_sender, $user_id_sender, $aps_type, $message) {
    	
    	$status = false;
    	
    	$deviceToken = $apns_id_receiver;
    	//echo $deviceToken;
		//echo $apns_id_sender;
    	$passphrase = 'silver';
    	
    	$message_params = array('message' => 'Message arrived');
    	
    	////////////////////////////////////////////////////////////////////////////////
    	
    	$ctx = stream_context_create();
    	stream_context_set_option($ctx, 'ssl', 'local_cert', './application/models/ck.pem');
    	stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    	// Open a connection to the APNS server
    	$fp = stream_socket_client(
    			'ssl://gateway.push.apple.com:2195', $err,
    			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
    	
//     	$fp = stream_socket_client(
//     	 'ssl://gateway.sandbox.push.apple.com:2195', $err,
//     			$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
    	
    	if(!$fp) exit("Failed to connect: $err $errstr" . PHP_EOL);
    	//echo 'Connected to APNS' . PHP_EOL;
    	// Create the payload body
    	$body['aps'] = array(
    			'alert' => $message,
    			'sound' => 'default',
    			'badgecount' => 1,
    			'info'=> $message_params,
				'apns_id_sender'=>$apns_id_sender,
				'user_id_sender'=>$user_id_sender,
				'aps_type'=>$aps_type,
    			'notify' => 'notification'
    	);
    	// Encode the payload as JSON
    	$payload = json_encode($body);
    	// Build the binary notification
    	$msg1 = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
    	// Send it to the server
    	$result_apns = fwrite($fp, $msg1, strlen($msg1));
    	if (!$result_apns) {
    		//echo 'Message not delivered' . PHP_EOL;
    		$status = false;
    	} else {
    		//echo 'Message successfully delivered' . PHP_EOL;
    		$status = true;
    	}
    	// Close the connection to the server
    	fclose($fp);
    	
    	return $status;
    	
    }	
    

    // Common Functions
	
	public function get_user_auth($password){
		return sha1(config_item('user_auth_salt') . md5($password));
	}
	
	
	public function get_address($lat, $lng, $timeoutParam = 0) {
    	$timeout = (($timeoutParam == 0) ? config_item('http_timeout_default') : $timeoutParam);
    	$arContext['http']['timeout'] = $timeout;
    	$context = stream_context_create($arContext);
        $url = 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($lng) . '&sensor=false';
        $json = @file_get_contents($url);
        $data = json_decode($json);
        $status = $data->status;
        if ($status == "OK")
            return $data->results[0]->formatted_address;
        else
            return false;
    }
    

    public function remove_characters($needle, $str) {
		$s = '';
		foreach(explode(',', $str) as $item){
			if(($item != $needle) && ($item != '')) {
				$s .= $item . ',';
			}
		}
    	//$s = str_replace($needle, '', $str);
    	//$s = $this->clean($s);
    	return $s;
    }
	
	public function distance($lat1, $lon1, $lat2, $lon2) {
		$theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return $miles;
    }

	/// Order Array of Array according desc or asc  ////

	// $result_each = $this->orderBy($result_each, 'winery_distance'); ///
	
	public function orderBy($data, $field){
        $code = "return strnatcmp(\$a['$field'], \$b['$field']);";
        usort($data, create_function('$a,$b', $code));
        return $data;
    }
}
