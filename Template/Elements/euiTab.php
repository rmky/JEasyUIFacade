<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiTab extends euiPanel {
	
	function generate_html(){
		$output = <<<HTML
	<div title="{$this->get_widget()->get_caption()}">
		{$this->build_html_for_children()}
	</div>
HTML;
		return $output;
	}
}
?>