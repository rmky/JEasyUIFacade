<?php
namespace exface\JEasyUiTemplate\Template\Elements;
use exface\Core\Widgets\InputSelect;

/**
 * The InputSelect widget will be rendered into a combobox in jEasyUI.
 * 
 * @method InputSelect get_widget()
 * 
 * @author Andrej Kabachnik
 *
 */
class euiInputSelect extends euiInput {
	
	protected function init(){
		parent::init();
		$this->set_element_type('combobox');
	}
	
	function generate_html(){
		$widget = $this->get_widget();
		$options = '';
		foreach ($widget->get_selectable_options() as $value => $text){
			if (!($this->get_widget()->get_multi_select() && count($this->get_widget()->get_values()) > 1)){
				$selected = strcasecmp($this->get_value_with_defaults(), $value) == 0 ? true : false;
			}
			$options .= '
					<option value="' . $value . '"' . ($selected ? ' selected="selected"' : '') . '>' . $text . '</option>';
		}

		$output = '	<select style="height: 100%; width: 100%;" class="easyui-' . $this->get_element_type() . '" 
						name="' . $widget->get_attribute_alias() . ($widget->get_multi_select() ? '[]' : '') . '"  
						id="' . $this->get_id() . '"  
						' . ($widget->is_required() ? 'required="true" ' : '') . '
						' . ($widget->is_disabled() ? 'disabled="disabled" ' : '') . '
						' . ($this->build_js_data_options() ? 'data-options="' . $this->build_js_data_options() . '" ' : '') . '>
						' . $options . '
					</select>
					';
		return $this->build_html_wrapper_div($output);
	}
	
	function generate_js(){
		$output = '';
		return $output;
	}
	
	public function build_js_value_getter(){
		return "$('#" . $this->get_id() . "')." . $this->get_element_type() . "('" . ($this->get_widget()->get_multi_select() ? 'getValues' : 'getValue') . "')";
	}
	
	public function build_js_data_options(){
		return "panelHeight: 'auto'"
				. ($this->get_widget()->get_multi_select() ? ", multiple:true" : '')
				. ($this->get_widget()->get_multi_select() && count($this->get_widget()->get_values()) > 1 ? ", value:['" . implode("'" . $this->get_widget()->get_multi_select_value_delimiter() . "'", $this->get_widget()->get_values()) . "']" : '');
	}
}
?>