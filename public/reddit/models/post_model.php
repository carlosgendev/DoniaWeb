<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class post_model extends MY_Model {
	public function __construct(){
		parent::__construct();
		$this->tb_accounts = "reddit_accounts";
		$this->tb_posts = "reddit_posts";
	}
}
