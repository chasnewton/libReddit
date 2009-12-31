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
			$params['http']['header'] = $params['http']['header'] . "Cookie: " . $this->cookiejar->toString() . "\r\n";
		
		$ctx = stream_context_create ($params);

		set_error_handler(array($this, 'HTTPerror'));
		$fp = fopen($url, 'rb', false, $ctx);
		restore_error_handler();

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
		$params = array ('http' => array ('method' => 'POST', 'content' => $data, 'header' => "Content-type: application/x-www-form-urlencoded\r\n" ) );
		
		return $this->request($url, $params);
	}
	
	public function get($url) {
		$params = array ('http' => array ('method' => 'GET') );
		
		return $this->request($url, $params);
	}

	public static function get($url) {
		$http = new HTTP(null);
		$http->get($url);
	}

	public static function post($url, $data) {
		$http = new HTTP(null);
		$http->post($url, $data);
	}
	public function HTTPerror($errno, $errstr, $errfile, $errline) {
		$exception = null;
		$timeout = "failed to open stream: HTTP request failed! ";

		if(preg_match("@HTTP request failed! HTTP/.+? 404@",$errstr))
			$exception = new HTTPException(404, "URL not found.");
		else if(preg_match("@HTTP request failed! HTTP/.+? 403@",$errstr))
			$exception = new HTTPException(403, "Forbidden.");
		else if(preg_match("@HTTP request failed! HTTP/.+? 408@",$errstr))
			$exception = new HTTPException(408, "Timeout.");
		else if(preg_match("@HTTP request failed! HTTP/.+? 500@",$errstr))
			$exception = new HTTPException(500, "Internal server error.");	
		else if(preg_match("@HTTP request failed! HTTP/.+? 502@",$errstr))
			$exception = new HTTPException(502, "Bad gateway.");
		else if(preg_match("@HTTP request failed! HTTP/.+? 503@",$errstr))
			$exception = new HTTPException(503, "Service Unavailable.");
		else if(preg_match("@HTTP request failed! HTTP/.+? 504@",$errstr))
			$exception = new HTTPException(504, "Gateway Timeout.");
		else if(strstr($errstr,"failed to open stream: Connection refused"))
			$exception = new TCPException("Connection refused.");
		// Hackish way to check for TCP Timeout. Check for the absense of
		// any HTTP errors and just assume the most common TCP error will be
		// a timeout.
		else if(strrpos($errstr, $timeout) === strlen($errstr) - strlen($timeout))
			$exception = new TCPException("Connection timed out.");
		else
			$exception = new HTTPException(-1, $errstr);

		if($exception != null) {
			$php_errormsg = (string)$exception;
			debug_print_backtrace();
			restore_error_handler();
			throw $exception;
		}

	}
}
class TCPException extends Exception {
	public $error_desc;

	public function __construct($error_desc) {
		$this->error_desc = $error_desc;
	}

	public function __toString() {
		return "TCP Error: {$this->error_desc}";
	}
}
class HTTPException extends Exception {
	public $error_code;
	public $error_desc;

	public function __construct($error_code, $error_desc) {
		$this->error_code = $error_code;
		$this->error_desc = $error_desc;
	}

	public function __toString() {
		return "HTTP Error {$this->error_code}: " . $this->error_desc;
	}
}
?>
