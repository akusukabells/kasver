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
		$db =new firebaseRDB('https://adadad-ef4fb-default-rtdb.asia-southeast1.firebasedatabase.app');
		$insert = $db->insert("Test",$result);
		$data = [
			'status_code' => $result['status_code']
		];
		if($result['status_code'] == 200){
			echo $result['status_code'];
		}
	}

	 

}
class firebaseRDB{
	function __construct($url=null) {
	   if(isset($url)){
		  $this->url = $url;
	   }else{
		  throw new Exception("Database URL must be specified");
	   }
	}
 
	public function grab($url, $method, $par=null){
	   $ch = curl_init();
	   curl_setopt($ch, CURLOPT_URL, $url);
	   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	   if(isset($par)){
		  curl_setopt($ch, CURLOPT_POSTFIELDS, $par);
	   }
	   curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	   curl_setopt($ch, CURLOPT_TIMEOUT, 120);
	   curl_setopt($ch, CURLOPT_HEADER, 0);
	   $html = curl_exec($ch);
	   return $html;
	   curl_close($ch);
	}
 
 
	public function insert($table, $data){
	   $path = $this->url."/$table.json";
	   $grab = $this->grab($path, "POST", json_encode($data));
	   return $grab;
	}
 
	public function update($table, $uniqueID, $data){
	   $path = $this->url."/$table/$uniqueID.json";
	   $grab = $this->grab($path, "PATCH", json_encode($data));
	   return $grab;
	}
 
	public function delete($table, $uniqueID){
	   $path = $this->url."/$table/$uniqueID.json";
	   $grab = $this->grab($path, "DELETE");
	   return $grab;
	}
 
	public function retrieve($dbPath, $queryKey=null, $queryType=null, $queryVal =null){
	   if(isset($queryType) && isset($queryKey) && isset($queryVal)){
		  $queryVal = urlencode($queryVal);
		  if($queryType == "EQUAL"){
				$pars = "orderBy=\"$queryKey\"&equalTo=\"$queryVal\"";
		  }elseif($queryType == "LIKE"){
				$pars = "orderBy=\"$queryKey\"&startAt=\"$queryVal\"";
		  }
	   }
	   $pars = isset($pars) ? "?$pars" : "";
	   $path = $this->url."/$dbPath.json$pars";
	   $grab = $this->grab($path, "GET");
	   return $grab;
	}
 
 }
