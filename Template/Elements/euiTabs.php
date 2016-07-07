<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiTabs extends euiContainer {
	
	function generate_html(){
		$output = <<<HTML
	<div id="{$this->get_id()}" class="easyui-tabs" data-options="fit:true,border:false">
		{$this->children_generate_html()}
	</div>
HTML;
		return $output;
	}
}
?>