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
	public function notif()
	{	
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

		//notification handler sample

		
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
		      } 
		      else {
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
			// TODO set payment status in merchant's database to 'Denied'
			echo "Payment using " . $type . " for transaction order_id: " . $order_id . " is expire.";
		  }

	}
 
 }