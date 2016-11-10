<?php namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputGroup extends euiPanel {
	
	public function generate_html(){
		$output = <<<HTML
	<div title="{$this->get_widget()->get_caption()}" class="grid fitem" style="height: {$this->get_height()}; width: {$this->get_width()}">
		{$this->build_html_for_children()}
	</div>
HTML;
		return $output;
	}
	
}
?>