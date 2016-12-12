<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiInputHidden extends euiInput {
	
	protected function init(){
		parent::init();
		$this->set_element_type('hidden');
	}
	
	function generate_html(){
		$output = '<input type="hidden" 
								name="' . $this->get_widget()->get_attribute_alias() . '" 
								value="' . $this->get_value() . '" 
								id="' . $this->get_id() . '" />';
		return $output;
	}
	
	function get_value() {
		if ($this->get_widget()->get_value_expression() && $this->get_widget()->get_value_expression()->is_reference()){
			$value = '';
		} else {
			$value = $this->get_widget()->get_value();
		}
		return $this->escape_string($value);
	}
	
	function generate_js(){
		$output .= $this->build_js_live_refrence();
		return $output;
	}
	
	function build_js_value_setter_method($value){
		return  'val(' . $value . ')';
	}
	
	function build_js_value_getter_method(){
		return  'val()';
	}
}