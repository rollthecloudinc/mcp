<?php
namespace UI;
require_once('Exception.php');
require_once('Element.php');

/*
* Manage user interface elements
*/
class Manager {

	private
	
	/*
	* Absolute path to UI element plugin directory
	*/
	$_dir
	
	/*
	* Determines whether exception will be thrown when
	* arguments that do not exist as an elements settings, are passed
	* via render. 
	*/
	,$_strict
	
	/*
	* Loaded UI elements
	*/
	,$_loaded = array();

	/*
	* @param str absolute path to element plugin directory
	*/
	public function __construct($dir,$strict=false) {
		$this->_dir = $dir;
		$this->_strict = $strict;
	}
	
	/*
	* Render UI element 
	*
	* @param str element name
	* @param array options
	* @return mix rendered element
	*/
	public function draw($name,$options) {
	
		/*
		* Get UI element plugin
		*/
		$element = $this->_getElement($name);
		
		/*
		* Extend settings
		*/
		$settings = $this->_extend($element->settings(),$options);
		
		/*
		* Render element
		*/
		return $element->html($settings,$this);
	
	}
	
	/*
	* Extend default UI element settings with argument overrides
	*
	* @param array UI element settings
	* @param array callee arguments
	* @return array final settings
	*/
	private function _extend($parent,$child) {
	
		// rebuilt configuration
		$rebuild = array();
		
		// settings required put not passed - error
		$missing = array();
		
		if($this->_strict === true && array_diff_key($child,$parent)) {
			throw new Exception\InvalidArguments(array_diff_key($child,$parent),$rebuild,$parent,$child);
		}
		
		foreach($parent as $name=>$cfg) {
		
			if(isset($cfg['required']) && $cfg['required'] === true && !array_key_exists($name,$child)) {
				$missing[] = $name;
				continue;
			}
		
			$rebuild[$name] = !isset($child[$name])?isset($cfg['default'])?$cfg['default']:null:$child[$name];
		}
		
		if(!empty($missing)) {
			throw new Exception\InvalidArguments($missing,$rebuild,$parent,$child);
		}
		
		return $rebuild;
	}
	
	/*
	* Get plugin object
	*
	* @param str name
	* @return obj UI element
	*/
	private function _getElement($name) {
		
		// replace . with php namespace delimiter
		$name = str_replace('.','\\',$name);
	
		// check to see if UI element has already been loaded
		if(isset($this->_loaded[$name])) return $this->_loaded[$name];
	
		// Build file location path
		$path = "{$this->_dir}/".str_replace('\\','/',$name).".php";
	
		// Make sure UI element declarion exists
		if(!file_exists($path)) {
			throw new Exception\InvalidFile($name,$path);
		}
		
		// include plugin directory
		require_once($path);
		
		// build class name
		$class = __NAMESPACE__."\\Element\\$name";
		
		// Make sure file exists
		if(!class_exists($class)) {
			throw new Exception\InvalidClass($name,$class,$path);
		}
		
		// Instantiate UI element
		$element = new $class();
		
		// Push onto loaded array
		$this->_loaded[$name] = $element;
		
		return $element;
		
	
	}

}
?>