<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiForm extends euiPanel {
	
	public function generate_html(){
		$output = '';
		if ($this->get_widget()->get_caption()){
			$output = '<div class="ftitle">' . $this->get_widget()->get_caption() . '</div>';
		}
		$output .= $this->form_generate_html();
		return $output;
	}
	
	function form_generate_html(){
		$output = '<form id="' . $this->get_id() . '"><div class="grid">';
		$output .= $this->build_html_for_widgets();
		$output .= '</div></form>';
		return $output;
	}
}
?>