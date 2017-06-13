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
        $widget = $this->getWidget();
        
        $children_html = $this->buildHtmlForChildren();
        
        // Wrap children widgets with a grid for masonry layouting - but only if there is something to be layed out
        if ($widget->countWidgets() > 1) {
            $children_html = <<<HTML

                        <div class="grid" id="{$this->getId()}_masonry_grid" style="width:100%;height:100%;">
                            {$children_html}
                            <div id="{$this->getId()}_sizer" style="width:calc(100%/{$this->getNumberOfColumns()});min-width:{$this->getWidthMinimum()}px;"></div>
                        </div>
HTML;
        }
        
        $output = <<<HTML
	<div title="{$widget->getCaption()}" data-options="{$this->buildJsDataOptions()}">
		{$children_html}
	</div>
HTML;
        return $output;
    }
    
    function buildJsDataOptions() {
        $widget = $this->getWidget();
        
        $output = parent::buildJsDataOptions() . ($widget->isHidden() || $widget->isDisabled() ? ', disabled:true' : '');
        return $output;
    }
}
?>