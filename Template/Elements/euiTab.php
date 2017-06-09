<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Tab;

/**
 *
 * @author Andrej Kabachnik
 *        
 * @method Tab getWidget()
 */
class euiTab extends euiPanel
{

    function generateHtml()
    {
        $options = $this->getWidget()->isHidden() || $this->getWidget()->isDisabled() ? 'disabled:true' : '';
        
        $output = <<<HTML
	<div title="{$this->getWidget()->getCaption()}" data-options="{$options}" class="grid" id="{$this->getId()}_masonry_grid">
		{$this->buildHtmlForChildren()}
	</div>
HTML;
        return $output;
    }
}
?>