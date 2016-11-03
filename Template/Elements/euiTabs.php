<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiTabs extends euiContainer {
	
	function generate_html(){
		$output = <<<HTML
	<div id="{$this->get_id()}" class="easyui-tabs" data-options="fit:true,border:false">
		{$this->build_html_for_children()}
	</div>
HTML;
		return $output;
	}
}
?>