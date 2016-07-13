<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiInputSelect extends euiInput {
	
	function init(){
		$this->set_element_type('combobox');
	}
	
	function generate_html(){
		/* @var $widget \exface\Core\Widgets\InputSelect */
		$widget = $this->get_widget();
		$options = '';
		foreach ($widget->get_selectable_options() as $value => $text){
			$options .= '
					<option value="' . $value . '"' . ($this->get_value_with_defaults() == $value ? ' selected="selected"' : '') . '>' . $text . '</option>';
		}

		$output = '	<select style="height: 100%; width: 100%;" class="textbox textbox-text textbox-prompt"
						name="' . $widget->get_attribute_alias() . '"  
						id="' . $this->get_id() . '"  
						' . ($widget->is_required() ? 'required="true" ' : '') . '
						' . ($widget->is_disabled() ? 'disabled="disabled" ' : '') . '
						' . ($this->generate_js_data_options() ? 'data_options="' . $this->generate_js_data_options() . '" ' : '') . '>
						' . $options . '
					</select>
					';
		return $this->generate_html_wrapper_div($output);
	}
	
	function generate_js(){
		$output = '';
		return $output;
	}
}
?>