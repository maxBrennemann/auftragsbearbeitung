<?php

/*
* source: 	https://stackoverflow.com/questions/27274157/new-google-recaptcha-with-checkbox-server-side-php
* 			https://developers.google.com/recaptcha/docs/display#js_api
*/

class VerifyReCaptcha {
	
	function __construct() {
		
	}
	
	public static function isValid() {
		try {
			$url = 'https://www.google.com/recaptcha/api/siteverify';
			$data = ['secret'   => '6LfA4EoUAAAAACtr31ltTsi7M4qcR62wUxym7Yi-',
					 'response' => $_POST['g-recaptcha-response'],
					 'remoteip' => $_SERVER['REMOTE_ADDR']];

			$options = [
				'http' => [
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => http_build_query($data) 
				]
			];

			$context  = stream_context_create($options);
			$result = file_get_contents($url, false, $context);
			
			/*$errors = json_decode($result)->error-codes;
			if(isset($errors)) {
				var_dump($errors);
			}*/
			
			return json_decode($result)->success;
		}
		catch (Exception $e) {
			return null;
		}
	}
	
}

?>