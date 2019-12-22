<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class pinterest_model extends MY_Model {
	public function __construct(){
		parent::__construct();
		$this->tb_accounts = "pinterest_accounts";
		$this->tb_posts = "pinterest_posts";
		include APPPATH."../public/pinterest/libraries/pinterestapi.php";
	}

	public function post_validator(){
		$accounts  = get_social_accounts("pinterest");
		$caption   = post("caption");
		$link      = post("link");
		$type      = post("type");
		$media     = $this->input->post("media");
		$errors = array();

		if(empty($accounts)) return $errors = 0;

		switch ($type) {
			case 'text':
				$errors[] = lang("Pinterest requires an image");

				break;

			case 'link':
				if($link == ""){
					$errors[] = lang("The URL is required");
				}

				if (!filter_var($link, FILTER_VALIDATE_URL)) {
					$errors[] = lang("The URL is not a valid");
				}

				if(empty($media)){
					$errors[] = lang("Pinterest requires an image");
				}
				break;

			case 'photo':
				if(empty($media)){
					$errors[] = lang("Pinterest requires an image");
				}

				break;

			case 'video':
				$errors[] = lang("Pinterest requires an image");
				
				break;
			
			default:
				$errors[] = lang("Please select a type to post");
				break;
		}

		return $errors;
	}

	public function post_handler(){
		$accounts 	  = get_social_accounts("pinterest");
		$media        = $this->input->post("media");
		$time_post    = post("time_post");
		$caption      = post("caption");
		$url          = post("link");
		$type         = ($url != "")?"link":"photo";

		if(!empty($accounts)){
			if(!post("is_schedule")){

				$result_all = array();
				foreach ($accounts as $account_id) {
					$account = $this->model->get("login_type,pid,access_token,id,app_id,app_secret,account,password,proxy,default_proxy", $this->tb_accounts, "ids = '".$account_id."' AND uid = '".session("uid")."'");
					if(!empty($account)){
						$data = array(
							"ids" => ids(),
							"uid" => session("uid"),
							"account" => $account->id,
							"board"   => $account->pid,
							"data" => json_encode(array(
										"media"   => $media,
										"caption" => $caption,
										"url"     => $url
									)),
							"time_post" => NOW,
							"delay" => 0,
							"time_delete" => 0,
							"changed" => NOW,
							"created" => NOW
						);
 						
						if($account->login_type == 3){
							$proxy_data = get_proxy($this->tb_accounts, $account->proxy, $account);
							$pin = new PinterestAPI();
							$pin->set_unofficial($account->account, $account->password, $proxy_data->use);
							$result = $pin->unofficial_post($data);
						}else{
							$pinterest_client_id = get_option("pinterest_app_id", "");
							$pinterest_client_secret = get_option("pinterest_app_secret", "");
							if($account->login_type == 2 && $account->app_id != "" && $account->app_secret != ""){
								$pinterest_client_id = $account->app_id;
								$pinterest_client_secret = $account->app_secret;
							}

							$pin = new PinterestAPI($pinterest_client_id, $pinterest_client_secret);
							$pin->set_access_token($account->access_token);
							$result = $pin->post($data);
						}
						
						if(is_array($result)){
							$data['status'] = 3;
							$data['result'] = json_encode(array("message" => $result['message']));

							//Save report
							update_setting("pin_post_error_count", get_setting("pin_post_error_count", 0) + 1);
							update_setting("pin_post_count", get_setting("pin_post_count", 0) + 1);

							$this->db->insert($this->tb_posts, $data);

							$result_all[] = $result;
						}else{
							$data['status'] = 2;
							$data['result'] = json_encode(array("message" => "successfully", "id" => $result, "url" => "https://www.pinterest.com/pin/".$result));

							//Save report
							update_setting("pin_post_success_count", get_setting("pin_post_success_count", 0) + 1);
							update_setting("pin_post_count", get_setting("pin_post_count", 0) + 1);
							update_setting("pin_post_photo_count", get_setting("pin_post_photo_count", 0) + 1);

							$this->db->insert($this->tb_posts, $data);

							$result_all[] = array(
					        	"status"  => "success",
					        	"message" => lang("post_successfully")
					        );
						}
					}else{
						$result_all[] = array(
				        	"status"  => "error",
				        	"message" => lang("processing_is_error_please_try_again")
				        );
					}
				}
				
				return $result_all;
			}else{

				foreach ($accounts as $account_id) {
					$account = $this->model->get("pid,access_token,id", $this->tb_accounts, "ids = '".$account_id."' AND uid = '".session("uid")."'");
					if(!empty($account)){
						$data = array(
							"ids" => ids(),
							"uid" => session("uid"),
							"account" => $account->id,
							"board"   => $account->pid,
							"data" => json_encode(array(
										"media"   => $media,
										"caption" => $caption,
										"url"     => $url
									)),
							"time_post" => get_timezone_system($time_post),
							"delay" => 0,
							"time_delete" => 0,
							"status" => 1,
							"changed" => NOW,
							"created" => NOW
						);

						$this->db->insert($this->tb_posts, $data);
					}
				}

				return array(
		        	"status"  => "success",
		        	"message" => lang('add_schedule_successfully')
		        );

			}

		}else{

			return array(
	        	"status"  => "error",
	        	"message" => lang("processing_is_error_please_try_again")
	        );

		}
	}

	public function post_previewer($link_info){
		$caption = post("caption");
		$link = post("link");
		$type = post("type");
		$medias = $this->input->post("media");

		$data = array(
			"ajax_load" => true,
			"type" => $type,
			"caption" => $caption,
			"medias" => $medias,
			"link_info" => $link_info
		);

		return array(
			"name" => "pinterest",
			"icon" => "fa fa-pinterest",
			"color" => "#cd2029",
			"content" => $this->load->view("../../../public/pinterest/views/post/preview", $data, true)
		);
	}
}
