<?php
require_once "CookieJar.php";

class HTTP {
	public function __construct($cookiejar = null) {
		if ($cookiejar == null)
			$cookiejar = new CookieJar();
		$self->cookiejar = $cookiejar;
	}

	private function request($url, $params) {
		// Handle cookie jar. 
		if ($self->cookiejar != null && is_object($self->cookiejar))
			$params ['http'] ['header'] = "Cookie: " . $self->cookiejar->toString();
		
		$ctx = stream_context_create ($params);
		$fp = fopen($url, 'rb', false, $ctx);
		
		if (!$fp)
			throw new Exception ("Problem with $url, $php_errormsg");
		
		$meta_response = stream_get_meta_data($fp);
		$response = stream_get_contents($fp);
		if ($response == false)
			throw new Exception ( "Problem reading data from $url, $php_errormsg" );
		
		// Maintain the cookiejar
		if($self->cookiejar != null)
			foreach ($meta_response['wrapper_data'] as $key => $value) 
				$self->cookiejar->merge(new CookieJar($value));
		
		return array('headers' => $meta_response, 'response' => $response);
	}
	
	public static function post($url, $data) {
		$params = array ('http' => array ('method' => 'POST', 'content' => $data ) );
		
		return HTTP::request($url, $params);
	}
	
	public static function get($url) {
		$params = array ('http' => array ('method' => 'GET') );
		
		return HTTP::request($url, $params);
	}
}
?>
