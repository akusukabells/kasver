<?php

$server_key = "SB-Mid-server-U4Rl-v_VLt-SlGQ1tL9vl4E_";
$is_production = false;
$api_url = $is_production ? 
  'https://app.midtrans.com/snap/v1/transactions' : 
  'https://app.sandbox.midtrans.com/snap/v1/transactions';
  
class Home extends CI_Controller {

	public function index()
	{
		$this->load->view('home');
	}
	public function payment()
	{
		$this->load->view('pay');
	}
	public function notification(){
		$params = array('server_key' => 'SB-Mid-server-U4Rl-v_VLt-SlGQ1tL9vl4E_', 'production' => false);
		$this->load->library('veritrans');
		$this->veritrans->config($params);
		$this->load->helper('url');
		echo 'test notification handler';
		$json_result = file_get_contents('php://input');
		$result = json_decode($json_result);

		if($result){
		$notif = $this->veritrans->status($result->order_id);
		}

		error_log(print_r($result,TRUE));
		$order_id = $result['order_id'];
		$db = new firebaseRDB('https://adadad-ef4fb-default-rtdb.asia-southeast1.firebasedatabase.app');
		$insert = $db->insert("Test",$result);
		$data = [
			'status_code' => $result['status_code']
		];
		if($result['status_code'] == 200){
			echo $result['status_code'];
		}
	}

	 

}
