<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Interfaces\Widgets\iFillEntireContainer;
use exface\Core\Widgets\AbstractWidget;

class euiDashboard extends euiPanel
{
    // px
    private $spacing = 10;
    // relative units
    private $containerDefaultHeight = 10;

    public function generateHtml()
    {
        $children_html = '';
        foreach ($this->getWidget()->getWidgets() as $subw) {
            $padding = round($this->getSpacing() / 2);
            // z.B. die Filter-Widgets der DataTables sind genau getWidthRelativeUnits breit und
            // wuerden sonst vom Rand teilweise verdeckt werden. (+2px fuer die Borders)
            $minWidth = $this->getWidthRelativeUnit() + $this->getSpacing() + 2;
            
            $children_html .= <<<HTML

                        <div class="fitem {$this->getId()}_masonry_db_fitem" style="width:{$this->getWidgetWidth($subw)};min-width:{$minWidth}px;height:{$this->getWidgetHeight($subw)};-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;padding:{$padding}px">
                            {$this->getTemplate()->generateHtml($subw)}
                        </div>
HTML;
        }
        
        // Wrap children widgets with a grid for masonry layouting - but only if there is something to be layed out
        if ($this->getWidget()->countWidgets() > 1) {
            $children_html = <<<HTML

                    <div class="grid" id="{$this->getId()}_masonry_grid">
                        {$children_html}
                        <div id="{$this->getId()}_sizer" style="width:calc(100% / 3);min-width:{$minWidth}px;"></div>
                    </div>
HTML;
        }
        
        $output = <<<HTML

				<div class="easyui-{$this->getElementType()}"
					id="{$this->getId()}"
					data-options="{$this->buildJsDataOptions()}, fit: true"
					title="{$this->getWidget()->getCaption()}">
					{$children_html}
				</div>
HTML;
        return $output;
    }

    public function buildJsLayouterFunction()
    {
        $output = <<<JS

    function {$this->getId()}_layouter() {
        if (!$("#{$this->getId()}_masonry_grid").data("masonry")) {
            if ($("#{$this->getId()}_masonry_grid").find(".{$this->getId()}_masonry_db_fitem").length > 0) {
                $("#{$this->getId()}_masonry_grid").masonry({
                    columnWidth: '#{$this->getId()}_sizer',
                    itemSelector: ".{$this->getId()}_masonry_db_fitem"
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

    public function getSpacing()
    {
        return $this->spacing;
    }

    public function getContainerDefaultHeight()
    {
        return $this->containerDefaultHeight;
    }

    protected function getWidgetWidth(AbstractWidget $widget)
    {
        $dimension = $widget->getWidth();
        if ($dimension->isRelative()) {
            switch ($dimension->getValue()) {
                case 1:
                    $width = 'calc(100% / 3)';
                    break;
                case 2:
                    $width = 'calc(100% * 2/3)';
                    break;
                case 3:
                case 'max':
                    $width = '100%';
            }
        } elseif ($dimension->isTemplateSpecific() || $dimension->isPercentual()) {
            $width = $dimension->getValue();
        } else {
            // "Grosse" oder "kleine" Widgets ohne angegebene Breite.
            $width = 'calc(100% / 3)';
        }
        return $width;
    }

    protected function getWidgetHeight(AbstractWidget $widget)
    {
        $dimension = $widget->getHeight();
        if ($dimension->isRelative()) {
            $height = $this->getHeightRelativeUnit() * $dimension->getValue() . 'px';
        } elseif ($dimension->isTemplateSpecific() || $dimension->isPercentual()) {
            $height = $dimension->getValue();
        } elseif ($widget instanceof iFillEntireContainer) {
            // Ein "grosses" Widget ohne angegebene Hoehe.
            $height = ($this->getHeightRelativeUnit() * $this->getContainerDefaultHeight()) . 'px';
        } else {
            // Ein "kleines" Widget ohne angegebene Hoehe.
            $height = ($this->getHeightRelativeUnit() * $this->getHeightDefault()) . 'px';
        }
        return $height;
    }
}
?>