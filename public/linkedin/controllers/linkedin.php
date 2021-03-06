<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class linkedin extends MX_Controller {
	public $table;
	public $module;
	public $module_name;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		
		$this->table = LINKEDIN_ACCOUNTS;
		$this->module = get_class($this);
		$this->module_name = lang("linkedin_accounts");
		$this->module_icon = "fab fa-linkedin-in";
		$this->load->model($this->module.'_model', 'model');

	}

	public function block_general_settings(){
		$data = array();
		$this->load->view('account/general_settings', $data);
	}

	public function block_list_account(){
		$data = array(
			'module'       => $this->module,
			'module_name'  => $this->module_name,
			'module_icon'  => $this->module_icon,
			'list_account' => $this->model->fetch("id, ids, pid, username, avatar, status, type, url", $this->table, "uid = '".session("uid")."'")
		);
		$this->load->view("account/index", $data);
	}
	
	public function oauth(){
		if(!permission($this->module."_enable")){
			redirect(cn("account_manager"));
		}
		
		$li = new LinkedinAPI(LINKEDIN_CLIENT_ID, LINKEDIN_CLIENT_SERECT);
		redirect($li->login_url());
	}

	public function add_account(){
		$li = new LinkedinAPI(LINKEDIN_CLIENT_ID, LINKEDIN_CLIENT_SERECT);
		$access_token = $li->get_access_token();
		$li->set_access_token($access_token);
		$userinfo     = $li->get_user_info();
		$companies     = $li->get_company();
		set_session("linkedin_access_token", $access_token);

		$data = array(
			"companies" => $companies,
			"userinfo" => $userinfo
		);

		$this->template->build('account/add_account', $data);
	}

	public function ajax_add_account(){
		$accounts = $this->input->post("accounts");
		$access_token = session("linkedin_access_token");

		if(empty($accounts)){
			ms(array(
	        	"status"  => "error",
	        	"message" => lang('please_select_at_least_one_item')
	        ));
		}

		if($access_token){
			$li = new LinkedinAPI(LINKEDIN_CLIENT_ID, LINKEDIN_CLIENT_SERECT);
			$li->set_access_token($access_token);
			$userinfo     = $li->get_user_info();
			

			$data = array(
				"userinfo" => $userinfo,
			);

			foreach ($accounts as $account) {
				if(!check_number_account($this->table)){
					ms(array(
						"status" => "error",
						"message" => lang("limit_social_accounts")
					));
				}

				$data = array();
				if($account == $userinfo->id){
					$firstName_param = (array)$userinfo->firstName->localized;
			        $lastName_param = (array)$userinfo->lastName->localized;

			        $firstName = reset($firstName_param);
			        $lastName = reset($lastName_param);
			        $fullname = $firstName." ".$lastName;

					$data = array(
						"uid"          => session("uid"),
						"ids"          => ids(),
						"pid"          => $userinfo->id,
						"type"     	   => "profile",
						"username"     => $fullname,
						"avatar"       => "https://ui-avatars.com/api?name=".$firstName."+".$lastName."&size=128&background=ff9b00&color=fff",
						"url"          => "https://www.linkedin.com",
						"access_token" => $access_token,
						"status"       => 1
					);

					$account = $this->model->get("*", $this->table, "pid = '".$userinfo->id."' AND uid = '".session("uid")."'");
					if(empty($account)){
						$data["created"] = NOW;
						$this->db->insert($this->table, $data);
					}else{
						$this->db->update($this->table, $data, array("id" => $account->id));
					}
				}else{

					$companies     = $li->get_company();
					if(!empty($companies)){
						foreach ($companies->elements as $company) {
							$company = (array)$company; 
                    		$company = $company['organizationalTarget~'];
                    		$logo = (array)$company->logoV2;
		                    $logo = $logo['original~'];
		                    $logo = $logo->elements[0]->identifiers[0]->identifier;

							if($company->id == $account){
								$data = array(
									"uid"          => session("uid"),
									"ids"          => ids(),
									"pid"          => $company->id,
									"type"     	   => "page",
									"username"     => $company->localizedName,
									"avatar"       => $logo,
									"url"          => "https://www.linkedin.com/company/".$company->id,
									"access_token" => $access_token,
									"status"       => 1
								);

								$account = $this->model->get("*", $this->table, "pid = '".$company->id."' AND uid = '".session("uid")."'");
								if(empty($account)){
									$data["created"] = NOW;
									$this->db->insert($this->table, $data);
								}else{
									$this->db->update($this->table, $data, array("id" => $account->id));
								}
							}
						}

					}

				}

			}

			ms(array(
				"status" => "success",
				"message" => lang('add_account_successfully')
			));
		}else{
			ms(array(
	        	"status"  => "error",
	        	"message" => lang('an_error_occurred_during_processing_please_try_again')
	        ));
		}
	}

	public function ajax_delete_item(){
		$this->model->delete($this->table, post("id"), false);
	}
}