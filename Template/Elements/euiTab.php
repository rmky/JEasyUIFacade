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
        
        $children_html = <<<HTML

            {$this->buildHtmlForChildren()}
            <div id="{$this->getId()}_sizer" style="width:calc(100%/{$this->getNumberOfColumns()});min-width:{$this->getMinWidth()};"></div>
HTML;
        
        // Wrap children widgets with a grid for masonry layouting - but only if there is something to be layed out
        if ($widget->countVisibleWidgets() > 1) {
            // masonry_grid-wrapper wird benoetigt, da die Groesse des Tabs selbst nicht
            // veraendert werden soll.
            $children_html = <<<HTML

        <div class="grid" id="{$this->getId()}_masonry_grid" style="width:100%;height:100%;">
            {$children_html}
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

    function buildJsDataOptions()
    {
        $widget = $this->getWidget();
        
        $output = parent::buildJsDataOptions() . ($widget->isHidden() || $widget->isDisabled() ? ', disabled:true' : '');
        return $output;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\euiPanel::buildJsLayouterFunction()
     */
    public function buildJsLayouterFunction()
    {
        $output = <<<JS

    function {$this->getId()}_layouter() {
        if (!$("#{$this->getId()}_masonry_grid").data("masonry")) {
            if ($("#{$this->getId()}_masonry_grid").find(".{$this->getId()}_masonry_fitem").length > 0) {
                $("#{$this->getId()}_masonry_grid").masonry({
                    columnWidth: "#{$this->getId()}_sizer",
                    itemSelector: ".{$this->getId()}_masonry_fitem"
                });
            }
        } else {
            $("#{$this->getId()}_masonry_grid").masonry("reloadItems");
            $("#{$this->getId()}_masonry_grid").masonry();
        }
    }
JS;
        
        return $output;
    }
}
?>