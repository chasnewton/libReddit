<?php
require_once "CookieJar.php";

class HTTP {
	private static function request($url, $cookiejar = null, $params) {
		// Handle cookie jar. 
		if ($cookiejar != null && is_object($cookiejar))
			$params ['http'] ['header'] = "Cookie: " . $cookiejar->toString();
		
		$ctx = stream_context_create ($params);
		$fp = fopen($url, 'rb', false, $ctx);
		
		if (!$fp)
			throw new Exception ("Problem with $url, $php_errormsg");
		
		$meta_response = stream_get_meta_data($fp);
		$response = stream_get_contents($fp);
		if ($response == false)
			throw new Exception ( "Problem reading data from $url, $php_errormsg" );
		
		// Maintain the cookiejar
		if($cookiejar != null)
			foreach ($meta_response['wrapper_data'] as $key => $value) 
				$cookiejar->merge(new CookieJar($value));
		
		return array('headers' => $meta_response, 'response' => $response);
	}
	
	public static function post($url, $data, $cookiejar = null) {
		$params = array ('http' => array ('method' => 'POST', 'content' => $data ) );
		
		return HTTP::request($url, $cookiejar, $params);
	}
	
	public static function get($url, $cookiejar = null) {
		$params = array ('http' => array ('method' => 'GET') );
		
		return HTTP::request($url, $cookiejar, $params);
	}
}
?>