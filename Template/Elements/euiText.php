<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiText extends euiAbstractElement {
	
	function generate_html(){
		$output = '<p>' . $this->get_widget()->get_text() . '</p>';
		return $output;
	}
	
	function generate_js(){
		return '';
	}
}
?>