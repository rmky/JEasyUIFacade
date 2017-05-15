<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Tab;

/**
 * 
 * @author Andrej Kabachnik
 *
 * @method Tab get_widget()
 */
class euiTab extends euiPanel {
	
	function generate_html(){
		
		$options = $this->get_widget()->is_hidden() || $this->get_widget()->is_disabled() ? 'disabled:true' : '';
		
		$output = <<<HTML
	<div title="{$this->get_widget()->get_caption()}" data-options="{$options}" class="grid">
		{$this->build_html_for_children()}
	</div>
HTML;
		return $output;
	}
}
?>