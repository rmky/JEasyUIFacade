<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiInputDateTime extends euiInputDate {
	
	function init(){
		parent::init();
		$this->set_element_type('datetimebox');
	}
	
	protected function build_js_date_format(){
		return 'yyyy-MM-dd HH:mm:ss';
	}
}