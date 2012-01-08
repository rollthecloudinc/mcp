<?php
class MCPField extends StdClass implements ArrayAccess {
	
	private 
	
	$_value
	,$_id;
	
	public function setValue($value) {
		$this->_value = $value;
	}
	
	public function setId($id) {
		$this->_id = $id;
	}
	
	public function getId() {
		return $this->_id;
	}
	
	public function __toString() {
		return (string) $this->_value;
	}
        
        public function offsetSet($offset, $value) {
            if (is_string($offset)) {
                $this->$offset = $value;
            }
        }
        
        public function offsetExists($offset) {
            return isset($this->$offset);
        }
        
        public function offsetUnset($offset) {
            unset($this->$offset);
        }
        
        public function offsetGet($offset) {
            return isset($this->$offset)?$this->$offset:null;
        }
	
	
}
?>
