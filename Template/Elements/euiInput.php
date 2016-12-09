<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiInput extends euiAbstractElement {
	
	protected function init(){
		parent::init();
		$this->set_element_type('textbox');
		// If the input's value is bound to another element via an expression, we need to make sure, that other element will
		// change the input's value every time it changes itself. This needs to be done on init() to make sure, the other element
		// has not generated it's JS code yet!
		$this->register_live_reference_at_linked_element();
	}
	
	function generate_html(){
		/* @var $widget \exface\Core\Widgets\Input */
		$widget = $this->get_widget();
		
		$output = '	<input style="height: 100%; width: 100%;"
						name="' . $widget->get_attribute_alias() . '" 
						value="' . $this->get_value_with_defaults() . '" 
						id="' . $this->get_id() . '"  
						' . ($widget->is_required() ? 'required="true" ' : '') . '
						' . ($widget->is_disabled() ? 'disabled="disabled" ' : '') . '
						/>
					';
		return $this->build_html_wrapper_div($output);
	}
	
	public function get_value_with_defaults(){
		if ($this->get_widget()->get_value_expression() && $this->get_widget()->get_value_expression()->is_reference()){
			$value = '';
		} else {
			$value = $this->get_widget()->get_value();
		}
		if ((is_null($value) || $value === '') && $this->get_widget()->get_attribute()){
			if (!$default_expr = $this->get_widget()->get_attribute()->get_fixed_value()){
				$default_expr = $this->get_widget()->get_attribute()->get_default_value();
			}
			if ($default_expr){
				if ($data_sheet = $this->get_widget()->get_prefill_data()){
					$value = $default_expr->evaluate($data_sheet, $this->get_widget()->get_attribute()->get_alias(), 0);
				} elseif ($default_expr->is_string()){
					$value = $default_expr->get_raw_value();
				}
			}
		} 
		return $this->escape_string($value);
	}
	
	protected function build_html_wrapper_div($html){
		if ($this->get_widget()->get_caption() && !$this->get_widget()->get_hide_caption()){
			$input = '
						<label>' . $this->get_widget()->get_caption() . '</label>
						<div class="exf_input_wrapper">' . $html . '</div>';
		} else {
			$input = $html;
		}
		
		$output = '	<div class="fitem exf_input" title="' . trim($this->build_hint_text()) . '" style="width: ' . $this->get_width() . '; height: ' . $this->get_height() . ';">
						' . $input . '
					</div>';
		return $output;
	}
	
	function generate_js(){
		$output = '';
		$output .= "
				$('#" . $this->get_id() . "')." . $this->get_element_type() . "(" . ($this->build_js_data_options() ? '{' . $this->build_js_data_options() . '}' : '') . ");//.textbox('addClearBtn', 'icon-clear');
				";
		$output .= $this->build_js_live_refrence();
		$output .= $this->build_js_on_change_handler();
		return $output;
	}
	
	protected function build_js_live_refrence(){
		$output = '';
		if ($this->get_widget()->get_value_expression() && $this->get_widget()->get_value_expression()->is_reference()){
			$link = $this->get_widget()->get_value_expression()->get_widget_link();
			$linked_element = $this->get_template()->get_element_by_widget_id($link->get_widget_id(), $this->get_page_id());
			if ($linked_element){
				$output = $this->build_js_value_setter($linked_element->build_js_value_getter($link->get_column_id())) . ";";
			}
		}
		return $output;
	}
	
	/**
	 * Makes sure, this element is always updated, once the value of a live reference changes - of course, only if there is a live reference!
	 * @return euiInput
	 */
	protected function register_live_reference_at_linked_element(){
		if ($linked_element = $this->get_linked_template_element()){
			$linked_element->add_on_change_script($this->build_js_live_refrence());
		}
		return $this;
	}
	
	public function get_linked_template_element(){
		$linked_element = null;
		if ($this->get_widget()->get_value_expression() && $this->get_widget()->get_value_expression()->is_reference()){
			$link = $this->get_widget()->get_value_expression()->get_widget_link();
			$linked_element = $this->get_template()->get_element_by_widget_id($link->get_widget_id(), $this->get_page_id());
		}
		return $linked_element;
	}
	
	function build_js_init_options(){
		return $this->build_js_data_options();
	}
	
	protected function build_js_data_options(){
		return '';
	}
	
	function build_js_value_setter_method($value){
		return  $this->get_element_type() . '("setValue", ' . $value . ')';
	}
	
	protected function build_js_on_change_handler(){
		if ($this->get_on_change_script()){
			return "$('#" . $this->get_id() . "').change(function(event){" . $this->get_on_change_script() . "});";
		} else {
			return '';
		}
	}
}
?>