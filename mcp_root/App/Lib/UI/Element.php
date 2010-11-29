<?php
namespace UI;
/*
* User interface element
*/
interface Element {
	// get settings 
	public function settings();
	public function html($settings,\UI\Manager $ui);
}
?>