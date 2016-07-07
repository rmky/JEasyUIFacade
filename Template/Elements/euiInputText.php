<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiInputText extends euiInput {
	
	function init(){
		parent::init();
		$this->set_element_type('textbox');
		$this->set_height_default(2);
	}
	
	function generate_html(){
		$output = ' <textarea 
							name="' . $this->get_widget()->get_attribute_alias() . '" 
							id="' . $this->get_id() . '"  
							style="height: calc(100% - 6px); width: calc(100% - 6px);"
							' . ($this->get_widget()->is_required() ? 'required="true" ' : '') . '
							' . ($this->get_widget()->is_disabled() ? 'disabled="disabled" ' : '') . '>'
						. $this->get_widget()->get_value() . 
					'</textarea>
					';
		return $this->generate_html_wrapper_div($output);;
	}
	
	function generate_js(){
		$output = '';
		$output .= $this->generate_js_live_refrence();
		$output .= $this->generate_js_on_change_handler();
		return $output;
	}
	
	public function get_js_value_setter_method($value){
		return 'val(' . $value . ')';
	}
	/*
	function generate_js_data_options(){
		return parent::generate_js_data_options() . ', multiline: true';
	}
	
	function get_js_value_setter_method($value){
		return  $this->get_element_type() . '("setText", ' . $value . ')';
	}*/
}