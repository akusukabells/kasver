<?php
defined('BASEPATH') OR exit('No direct script access allowed');
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
		if( !strpos($_SERVER['REQUEST_URI'], '/charge') ) {
			http_response_code(404); 
			echo "wrong path, make sure it's `/charge`"; exit();
		  }
		  
		  if( $_SERVER['REQUEST_METHOD'] !== 'POST'){
			http_response_code(404);
			echo "Page not found or wrong HTTP request method is used"; exit();
		  }
		  $request_body = file_get_contents('php://input');
			header('Content-Type: application/json');

		$charge_result = chargeAPI($api_url, $server_key, $request_body);

		http_response_code($charge_result['http_code']);

		echo $charge_result['body'];
	}
	function chargeAPI($api_url, $server_key, $request_body){

		$ch = curl_init();
		$curl_options = array(
		  CURLOPT_URL => $api_url,
		  CURLOPT_RETURNTRANSFER => 1,
		  CURLOPT_POST => 1,
		  CURLOPT_HEADER => 0,
	  
	  
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Basic ' . base64_encode($server_key . ':')
		  ),
		  CURLOPT_POSTFIELDS => $request_body
		);
		curl_setopt_array($ch, $curl_options);
		$result = array(
		  'body' => curl_exec($ch),
		  'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
		);
		return $result;
	  }
	  public function notification(){
		require_once('Home.php');
		Veritrans_Config::$isProduction = false;
		Veritrans_Config::$serverKey = 'SB-Mid-server-U4Rl-v_VLt-SlGQ1tL9vl4E_';
		$notif = new Veritrans_Notification();
		$transaction = $notif->transaction_status;
		$type = $notif->payment_type;
		$order_id = $notif->order_id;
		$fraud = $notif->fraud_status;

		if ($transaction == 'capture') {
 			 // For credit card transaction, we need to check whether transaction is challenge by FDS or not
				 if ($type == 'credit_card'){
  					  if($fraud == 'challenge'){
    					  // TODO set payment status in merchant's database to 'Challenge by FDS'
    					  // TODO merchant should decide whether this transaction is authorized or not in MAP
   					   echo "Transaction order_id: " . $order_id ." is challenged by FDS";
    				  } else {
      				// TODO set payment status in merchant's database to 'Success'
      				echo "Transaction order_id: " . $order_id ." successfully captured using " . $type;
      }
    }
  }
else if ($transaction == 'settlement'){
  // TODO set payment status in merchant's database to 'Settlement'
  echo "Transaction order_id: " . $order_id ." successfully transfered using " . $type;
  }
  else if($transaction == 'pending'){
  // TODO set payment status in merchant's database to 'Pending'
  echo "Waiting customer to finish transaction order_id: " . $order_id . " using " . $type;
  }
  else if ($transaction == 'deny') {
  // TODO set payment status in merchant's database to 'Denied'
  echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is denied.";
  }
  else if ($transaction == 'expire') {
  // TODO set payment status in merchant's database to 'expire'
  echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is expired.";
  }
  else if ($transaction == 'cancel') {
  // TODO set payment status in merchant's database to 'Denied'
  echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is canceled.";
}
	  }

}
