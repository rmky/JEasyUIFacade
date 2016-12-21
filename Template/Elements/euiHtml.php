<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiHtml extends euiText {
	
	function init(){
	
	}
	
	function generate_html(){
		$output = '';
		if ($this->get_widget()->get_css()){
			$output .= '<style>' . $this->get_widget()->get_css() . '</style>';
		}
		if ($this->get_widget()->get_caption() && !$this->get_widget()->get_hide_caption()){
			$output .= '<label for="' . $this->get_id() . '">' . $this->get_widget()->get_caption() . '</label>';
		}
		
		$output .= '<div id="' . $this->get_id() . '">' . $this->get_widget()->get_html() . '</div>';
		return $output;
	}
	
	function generate_js(){
		return $this->get_widget()->get_javascript();
	}
	
}
?>