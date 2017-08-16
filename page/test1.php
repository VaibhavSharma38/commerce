<?php

namespace xepan\commerce;

Class page_test1 extends \xepan\base\Page{

	function init(){
		parent::init();		
		
		$service_url = 'http://localhost/saraswatiiglobal/api/endpoint/?page=v1_customer';
		// $response = file_get_contents($service_url);
		// echo "<pre>";
		// print_r($response);
		// exit;

		// $this->APIGET($service_url);
		// $this->APIPOST($service_url);			
		// $this->APIPUT($service_url);			
		// $this->APIDELETE($service_url);			
		
	}

	function APIGET($service_url){
		$curl = curl_init($service_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_response = curl_exec($curl);
		
		if ($curl_response === false) {
		    $info = curl_getinfo($curl);
		    curl_close($curl);
		    die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}
		
		curl_close($curl);
		$decoded = json_decode($curl_response);
		
		if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
		    die('error occured: ' . $decoded->response->errormessage);
		}
		
		echo 'response ok!';

		echo "<pre>";
		print_r($decoded);
		exit;
	}

	function APIPOST($service_url){
		$curl = curl_init($service_url);
		$curl_post_data = array(
				            'first_name' => 'Test Customer2',
				            'status' => 'Active',
				            'organization' => 'TEST COMPANY'
		);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);

		$curl_response = curl_exec($curl);
		
		if ($curl_response === false) {
		    $info = curl_getinfo($curl);
		    curl_close($curl);
		    die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}
		
		curl_close($curl);
		$decoded = json_decode($curl_response);
		if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
		    die('error occured: ' . $decoded->response->errormessage);
		}
		echo 'response ok!';
		
		echo "<pre>";
		print_r($decoded);
		exit;
	}

	function APIPUT($service_url){
		$ch = curl_init($service_url);
		 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		
		$data = array("name" => 'R');
		curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($data));
		$response = curl_exec($ch);
		
		if ($response === false) {
		    $info = curl_getinfo($ch);
		    curl_close($ch);
		    die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}
		
		curl_close($ch);
		$decoded = json_decode($response);
		
		if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
		    die('error occured: ' . $decoded->response->errormessage);
		}
		
		echo 'response ok!';

		echo "<pre>";
		print_r($decoded);
		exit;
	}

	function APIDELETE($service_url){		
		$ch = curl_init($service_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		
		$curl_post_data = array(
		        'name' => 'Test Customer2'
		);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_post_data);
		$response = curl_exec($ch);
		
		if ($response === false) {
		    $info = curl_getinfo($curl);
		    curl_close($curl);
		    die('error occured during curl exec. Additioanl info: ' . var_export($info));
		}
		
		curl_close($ch);
		$decoded = json_decode($response);
		
		if (isset($decoded->response->status) && $decoded->response->status == 'ERROR') {
		    die('error occured: ' . $decoded->response->errormessage);
		}
		
		echo 'response ok!';

		echo "<pre>";
		print_r($decoded);
		exit;
	}
}