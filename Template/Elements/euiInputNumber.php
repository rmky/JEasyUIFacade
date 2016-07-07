<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiInputNumber extends euiInput {
	
	function init(){
		parent::init();
		$this->set_element_type('numberbox');
	}
	
	protected function generate_js_data_options(){
		$output = parent::generate_js_data_options();
		if ($output){
			$output .= ', ';
		}
		
		$output .= "precision: '" . $this->get_widget()->get_precision() . "'
					, decimalSeparator: ','
				";
		return $output;
	}
}