<?php
namespace exface\JEasyUiTemplate\Template\Elements;

/**
 * The Form widget is just another panel in jEasyUI. The HTML form cannot be used here, because form widgets can contain
 * tabs and the tabs implementation in jEasyUI is using HTML forms, so it does not work within a <form> element.
 * 
 * @author Andrej Kabachnik
 *
 */
class euiForm extends euiPanel {
	
	function build_html_buttons(){
		$output = '';
		foreach ($this->get_widget()->get_buttons() as $btn){
			$output .= $this->get_template()->generate_html($btn);
		}
	
		return $output;
	}
	
	function build_js_buttons(){
		$output = '';
		foreach ($this->get_widget()->get_buttons() as $btn){
			$output .= $this->get_template()->generate_js($btn);
		}
	
		return $output;
	}
}
?>