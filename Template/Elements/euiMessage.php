<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiMessage extends euiText {
	
	function generate_html(){
		if ($this->get_widget()->get_width()->to_string()){
			$width = $this->get_width();
		} else {
			$width = 'calc(100% - 20px)';
		}
		$output = '
				<div class="messager-body fitem" style="width:' . $width . '">
					<div class="messager-icon ' . $this->get_css_message_type() . '"></div>
					<div>' . $this->get_widget()->get_text() . '</div>
				</div>';
		return $output;
	}
	
	function get_css_message_type(){
		switch ($this->get_widget()->get_type()){
			case EXF_MESSAGE_TYPE_ERROR: $output = 'messager-error'; break;
			case EXF_MESSAGE_TYPE_WARNING: $output = 'messager-warning'; break;
			case EXF_MESSAGE_TYPE_INFO: $output = 'messager-info'; break;
			case EXF_MESSAGE_TYPE_SUCCESS: $output = 'messager-success'; break;
		}
		return $output;
	}
}
?>