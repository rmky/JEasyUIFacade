<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiInputHidden extends euiInput {
	
	function init(){
		parent::init();
		$this->set_element_type('hidden');
	}
	
	function generate_html(){
		$output = '<input type="hidden" 
								name="' . $this->get_widget()->get_attribute_alias() . '" 
								value="' . $this->get_value_with_defaults() . '" 
								id="' . $this->get_id() . '" />';
		return $output;
	}
	
	function generate_js(){
		$output .= $this->generate_js_live_refrence();
		return $output;
	}
	
	function get_js_value_setter_method($value){
		return  'val(' . $value . ')';
	}
	
	function get_js_value_getter_method(){
		return  'val()';
	}
}