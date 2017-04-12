<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\AbstractAjaxTemplate\Template\Elements\JqueryFormTrait;
use exface\Core\Widgets\Form;

/**
 * The Form widget is just another panel in jEasyUI. The HTML form cannot be used here, because form widgets can contain
 * tabs and the tabs implementation in jEasyUI is using HTML forms, so it does not work within a <form> element.
 * 
 * @method Form get_widget()
 * 
 * @author Andrej Kabachnik
 *
 */
class euiForm extends euiPanel {
	
	use JqueryFormTrait;
	
	public function generate_html(){
		return parent::generate_html() . $this->build_html_footer();
	}
	
	protected function build_html_footer(){
		$output = '';
		if ($this->get_widget()->has_buttons()){
			$output = <<<HTML

				<div id="{$this->get_footer_id()}" style="padding:5px;">
					{$this->build_html_buttons()}
				</div>

HTML;
		}
		return $output;
	}
	
	protected function has_footer(){
		if ($this->get_widget()->has_buttons()){
			return true;
		}
		return false;
	}
	
	protected function get_footer_id(){
		return $this->get_id() . '_footer';
	}
	
	public function build_js_data_options(){
		$options = parent::build_js_data_options();
		
		if ($this->has_footer()){
			$options .= ", footer: '#" . $this->get_footer_id() . "'"; 
		}
		
		return $options;
	}
}
?>