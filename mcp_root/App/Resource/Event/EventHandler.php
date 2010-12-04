<?php 
/*
* Manages events 
*/
class MCPEventHandler extends MCPResource {
	
	private
	
	/*
	* Stack of object hashes with event keys 
	* and associated handlers to each event.
	*/
	$_arrEvents;
	
	public function __construct(MCP $objMCP) {
		parent::__construct($objMCP);
		$this->_init();
	}
	
	private function _init() {
		$this->_arrEvents = array();
	}
	
	/*
	* Get objects unique foorprint for reference in events array 
	* 
	* @param obj object to get unique foot print for
	* @return str unique object foot print
	*/
	private function _getFootprint($obj) {
		return spl_object_hash($obj);
	}
	
	/*
	* Subscribe handler to event for object
	* 
	* @param obj listen to this object
	* @param str for this event
	* @param arr [obj,method] call this handler on event
	*/
	public function subscribe($objDispatcher,$strEvt,$arrHandler) {
		$strFootprint = $this->_getFootprint($objDispatcher);
		$this->_arrEvents[$strFootprint][$strEvt][] = $arrHandler; 
	}
	
	/*
	* Unsubscribe handler for event for object
	* 
	* @param obj listen to this object
	* @param str for this event
	* @param arr [obj,method] call this handler on event
	*/
	public function unsubscribe($objDispatcher,$strEvt,$arrHandler) {
		$strFootprint = $this->_getFootprint($objDispatcher);
		
		if(!isset($this->_arrEvents[$strFootprint],$this->_arrEvents[$strFootprint][$strEvt])) return;
		
		$arrKeep = array();
		foreach($this->_arrEvents[$strFootprint][$strEvt] as $intIndex=>$arrCallback) {
			/*
			* Skip over callback that matches unsubscription 
			*/
			if($arrCallback[0] === $arrHandler[0] && strcmp($arrCallback[1],$arrHandler[1]) == 0) continue;
			
			/*
			* Push those callbacks not matched into new array 
			*/
			$arrKeep[] = $arrCallback;
		}
		
		/*
		* Reassign all none removed callbacks 
		*/
		$this->_arrEvents[$strFootprint][$strEvt] = $arrKeep;
		
	}
	
	/*
	* Fire event
	* 
	* @param obj target
	* @param str event name
	*/
	public function fire($objTarget,$strEvt) {
		
		$strTarget = $this->_getFootprint($objTarget);
		
		if(isset($this->_arrEvents[$strTarget],$this->_arrEvents[$strTarget][$strEvt])) {
			
			/*
			* Call event listeners 
			*/
			foreach($this->_arrEvents[$strTarget][$strEvt] as $arrHandler) {
				// closure support added
				if(is_array($arrHandler)) {
					array_shift($arrHandler)->{array_shift($arrHandler)}(array('target'=>$objTarget,'event'=>$strEvt));
				} else {
					call_user_func($arrHandler,array('target'=>$objTarget,'event'=>$strEvt));
				}
			}
			
			/*
			* Get parent to bubble to
			*/
			$objBubbleTarget = $objTarget instanceof MCPResource?$objTarget->getBubbleTarget():null;
			
			if($objBubbleTarget === null) {
				return;
			}
			
			/*
			* Bubble event 
			*/
			foreach($this->_arrEvents[$strTarget][$strEvt] as $arrHandler) {
				$this->fire($objBubbleTarget,$strEvt);
			}
		}
		
	}
	
}
?>