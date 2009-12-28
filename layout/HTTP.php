<?php
require_once "CookieJar.php";

class HTTP {
	public function __construct($cookiejar = null) {
		if ($cookiejar == null)
			$cookiejar = new CookieJar();
		$this->cookiejar = $cookiejar;
	}

	private function request($url, $params) {
		// Handle cookie jar. 
		if ($this->cookiejar != null && is_object($this->cookiejar))
			$params ['http'] ['header'] = "Cookie: " . $this->cookiejar->toString();
		
		$ctx = stream_context_create ($params);
		$fp = fopen($url, 'rb', false, $ctx);
		
		if (!$fp)
			throw new Exception ("Problem with $url, $php_errormsg");
		
		$meta_response = stream_get_meta_data($fp);
		$response = stream_get_contents($fp);
		if ($response == false)
			throw new Exception ( "Problem reading data from $url, $php_errormsg" );
		
		// Maintain the cookiejar
		if($this->cookiejar != null)
			foreach ($meta_response['wrapper_data'] as $key => $value) 
				$this->cookiejar->merge(new CookieJar($value));
		
		return array('headers' => $meta_response, 'response' => $response);
	}
	
	public function post($url, $data) {
		$params = array ('http' => array ('method' => 'POST', 'content' => $data ) );
		
		return $this->request($url, $params);
	}
	
	public function get($url) {
		$params = array ('http' => array ('method' => 'GET') );
		
		return $this->request($url, $params);
	}
}
?>
