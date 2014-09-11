<?php
	/**
	* 
	*/
	class Thalamus
	{
		private static $cookies = array();
		public $config = array(
			
		);
		public $lastResponse = null;
		private $curl;

		function __construct($init_config)
		{
			@session_start();
			$this->setConfig($init_config);
		}

		private static function curlCallback($curl,$header){
			if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $header, $cookie) == 1)
    		self::$cookies[] = $cookie[1];
			return strlen($header); // Needed by curl

		}

		public function setConfig($config){
			$this->config = array_merge($this->config,$config);
		}

		public function api($api_method, $data = array(), $method = "POST", $version = 'v3'){
			$querystring = http_build_query(array(
				'touchpoint' => $this->config['touchpoint'],
				'token' => $this->config['token']
			));
			$opts = array(
				CURLOPT_URL => $this->config['api_url'].$version.'/'.$api_method.'?'.$querystring,
				CURLOPT_FOLLOWLOCATION => TRUE,
				CURLOPT_HEADERFUNCTION => 'Thalamus::curlCallback',
				CURLOPT_RETURNTRANSFER => TRUE,

				// ARE THESE EVEN NECESSARY?
				CURLOPT_USERAGENT => "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)",
				CURLOPT_SSL_VERIFYPEER => FALSE,
				CURLOPT_SSL_VERIFYHOST => FALSE,
				CURLOPT_COOKIEJAR => dirname(__FILE__) . '/cookies/cookie'.session_id().'.txt',
				CURLOPT_COOKIEFILE => dirname(__FILE__) . '/cookies/cookie'.session_id().'.txt'
			);
			if( isset ( $_SESSION['THALAMUS_COOKIES'] )){
				$opts[CURLOPT_COOKIE] = $_SESSION['THALAMUS_COOKIES'];
			}
			switch($method){
				
				case 'POST':
						$opts[CURLOPT_POST] = TRUE;
						$opts[CURLOPT_POSTFIELDS] = json_encode($data);
						$opts[CURLOPT_HTTPHEADER] = array(
							'Content-Type: application/json'
						);
					break;

				case 'PUT':
						$opts[CURLOPT_CUSTOMREQUEST] = "PUT";
						$opts[CURLOPT_HEADER] = FALSE;
						$opts[CURLOPT_POSTFIELDS] = json_encode($data);
						$opts[CURLOPT_HTTPHEADER] = array(
							'Content-Type: application/json'
						);
					break;

				default:
						$opts[CURLOPT_URL] .= '&'.http_build_query($data);
						$opts[CURLOPT_HTTPGET] = TRUE;
					break;
			}
			unset($this->curl);
			$this->curl = curl_init();
			curl_setopt_array($this->curl,$opts);
			$result = curl_exec($this->curl);
			
			$this->lastResponse = array(
				'error' => curl_error($this->curl),
				'debug' => curl_getinfo($this->curl)
			); 

			curl_close($this->curl);

			if( ! empty( self::$cookies ) ) {
				$_SESSION['THALAMUS_COOKIES'] = implode('; ',self::$cookies);
			}

			return json_decode($result);
		}

		public function getCountries(){
			return $this->api('referencedata/countries',array(),'GET','v1');
		}

		public function getCities( $state_id ){
			return $this->api('referencedata/states/'.$state_id.'/cities',array(),'GET');
		}

		public function getStates( $country_id = false){
			if( ! $country_id) $country_id = $this->config['country'];
			return $this->api('referencedata/countries/'.$country_id.'/states',array(),'GET','v1');
		}

		public function getUser($force = FALSE) {
			if( ! $force && isset($_SESSION['THALAMUS_USER']) ){
				return $_SESSION['THALAMUS_USER'];
			}else{
				$result = $this->api('person',array(),'GET','v1');
				if( isset ( $result->person ) ){
					$_SESSION['THALAMUS_USER'] = $result->person;
					return $result->person;
				}else{
					return false;
				}
			}
		}

		public function updateUser($user) {
			return $this->api('person',$user,'PUT');
		}

		public function logout(){
			if( array_key_exists('THALAMUS_COOKIES', $_SESSION) ){
				unset($_SESSION['THALAMUS_COOKIES']);
				unset($_SESSION['THALAMUS_USER']);
			}
		}

		public function register($data){
			$result = $this->api('person',$data);
			return $result;
		}

		public function login( $main, $password ){
			$result = $this->api('signin',array(
				'principal' => $main,
				'password' => $password
			));
			if( ! isset($result->person) ) return false;
			$_SESSION['THALAMUS_USER'] = $result->person;
			return $result;
		}

		public function requestResetPassword( $main ){
			return $this->api('person/password/requestreset',array('principal'=>$main),'POST','v1');
		}

		public function resetPassword( $main, $newPassword, $token ){
			return $this->api('person/password',array(
				'token' => $token,
				'password' => $newPassword,
				'principal' => $main
			),'PUT','v1');
		}

		public function getFBLoginURL( $callback_url ) {
			return $this->api('signin/facebook',array(
					'callback_url' => $callback_url
			),'GET');
		}
	}

?>