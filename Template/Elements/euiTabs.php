<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Tabs;

/**
 * 
 * @author Andrej Kabachnik
 * 
 * @method Tabs get_widget()
 *
 */
class euiTabs extends euiContainer {
	
	public function generate_html(){
		$output = <<<HTML
	<div id="{$this->get_id()}" class="easyui-tabs" data-options="fit:true,border:false">
		{$this->build_html_for_children()}
	</div>
HTML;
		return $output;
	}
	
	/*public function generate_js(){
		$js = parent::generate_js();
		
		foreach ($this->get_widget()->get_tabs() as $nr => $tab){
			if ($tab->is_hidden()){
				$js .= <<<JS

			$('#{$this->get_id()}').tabs('close', {$nr});

JS;
			}
		}
	}*/
}
?>