<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Panel;

/**
 * The Panel widget is mapped to a panel in jEasyUI
 *
 * @author Andrej Kabachnik
 *        
 * @method Panel getWidget()
 */
class euiPanel extends euiContainer
{

    private $on_load_script = '';

    private $on_resize_script = '';
    
    protected function init()
    {
        parent::init();
        $this->setElementType('panel');
    }

    public function generateHtml()
    {
        $widget = $this->getWidget();
        
        $children_html = $this->buildHtmlForWidgets();
        
        // Wrap children widgets with a grid for masonry layouting - but only if there is something to be layed out
        if ($widget->countWidgets() > 1) {
            $columnWidth = 'calc(100% / ' . $widget->getNumberOfColumns() . ')';
            $children_html = <<<HTML

                        <div class="grid" id="{$this->getId()}_masonry_grid" style="width:100%;height:100%">
                            {$children_html}
                            <div id="{$this->getId()}_sizer" style="width:{$columnWidth};min-width:{$this->getMinWidth()};"></div>
                        </div>
HTML;
        }
        
        // A standalone panel will always fill out the parent container (fit: true), but
        // other widgets based on a panel may not do so. Thus, the fit data-option is added
        // here, in the generate_html() method, which is verly likely to be overridden in
        // extending classes!
        $output = <<<HTML

                <div class="fitem {$this->getMasonryItemClass()}" style="width:{$this->getWidth()};min-width:{$this->getMinWidth()};height:{$this->getHeight()};padding:{$this->getPadding()};box-sizing:border-box;">
                    <div class="easyui-{$this->getElementType()}"
                            id="{$this->getId()}"
                            data-options="{$this->buildJsDataOptions()}, fit: true"
                            title="{$this->getWidget()->getCaption()}">
                        {$children_html}
                    </div>
                </div>
HTML;
        
        return $output;
    }

    public function generateJs()
    {
        $output = parent::generateJs();
        
        $output .= <<<JS

        {$this->buildJsLayouterFunction()}
JS;
        
        return $output;
    }

    /**
     * Generates the contents of the data-options attribute (e.g.
     * iconCls, collapsible, etc.)
     *
     * @return string
     */
    function buildJsDataOptions()
    {
        /** @var Panel $widget */
        $widget = $this->getWidget();
        
        if ($widget->getNumberOfColumns() != 1) {
            $this->addOnLoadScript($this->buildJsLayouter());
            $this->addOnResizeScript($this->buildJsLayouter());
        }
        $collapsibleScript = 'collapsible: ' . ($widget->isCollapsible() ? 'true' : 'false');
        $iconClassScript = $widget->getIconName() ? ', iconCls:"' . $this->buildCssIconClass($widget->getIconName()) . '"' : '';
        $onLoadScript = $this->getOnLoadScript() ? ', onLoad: function(){' . $this->getOnLoadScript() . '}' : '';
        $onResizeScript = $this->getOnResizeScript() ? ', onResize: function(){' . $this->getOnResizeScript() . '}' : '';
        
        return $collapsibleScript . $iconClassScript . $onLoadScript . $onResizeScript;
    }

    public function generateHeaders()
    {
        $includes = parent::generateHeaders();
        if ($this->getWidget()->getNumberOfColumns() != 1) {
            $includes[] = '<script type="text/javascript" src="exface/vendor/bower-asset/masonry/dist/masonry.pkgd.min.js"></script>';
        }
        return $includes;
    }

    public function getOnLoadScript()
    {
        return $this->on_load_script;
    }

    public function addOnLoadScript($value)
    {
        $this->on_load_script .= $value;
        return $this;
    }

    public function getOnResizeScript()
    {
        return $this->on_resize_script;
    }

    public function addOnResizeScript($value)
    {
        $this->on_resize_script .= $value;
        return $this;
    }

    public function buildJsLayouter()
    {
        return $this->getId() . '_layouter()';
    }

    public function buildJsLayouterFunction()
    {
        $output = <<<JS

    function {$this->getId()}_layouter() {
        if (!$("#{$this->getId()}_masonry_grid").data("masonry")) {
            if ($("#{$this->getId()}_masonry_grid").find(".{$this->getId()}_masonry_fitem").length > 0) {
                $("#{$this->getId()}_masonry_grid").masonry({
                    columnWidth: '#{$this->getId()}_sizer',
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