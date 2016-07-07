<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiTab extends euiPanel {
	
	function generate_html(){
		$output = <<<HTML
	<div title="{$this->get_widget()->get_caption()}">
		{$this->children_generate_html()}
	</div>
HTML;
		return $output;
	}
}
?>