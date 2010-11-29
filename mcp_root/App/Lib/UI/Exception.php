<?php
namespace UI\Exception;

/*
* Thrown when UI element file was found, but expected class doesn't exist
*/
class InvalidClass extends \Exception {
	
	/*
	* @param str requested UI element name
	* @param str expected class name
	* @param str absolute path to UI element file 
	*/
	public function __construct($name,$class,$path) {
		parent::__construct("$name:$class:$path");
	}
	
}

/*
* Thrown when UI element expected file not found
*/
class InvalidFile extends \Exception {
	
	/*
	* @param name of requested UI element
	* @param str expected absolute path of UI element class file
	*/
	public function __construct($name,$path) {
		parent::__construct("$name:$path");
	}
	
}

/*
* Thrown when render signature doesn't match UI plugin expected signature
*/
class InvalidArguments extends \Exception {
	
	/*
	* @param array misssing, required settings
	* @param array rebuilt array w/o missing settings
	* @param array all UI element settings - base config
	* @param array original setting overrides passed by user, to render
	*/
	public function __construct($missing,$rebuild,$parent,$child) {
		parent::__construct('missing: '.implode(',',$missing));
	}
	
}
?>