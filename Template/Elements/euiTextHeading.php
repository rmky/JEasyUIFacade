<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiTextHeading extends euiText {
	
	function generate_html(){
		$output = '';
		$output .= '<h' . $this->get_widget()->get_heading_level() . ' id="' . $this->get_id() . '">' . $this->get_widget()->get_text() . '</h' . $this->get_widget()->get_heading_level() . '>';
		return $output;
	}
	
}
?>