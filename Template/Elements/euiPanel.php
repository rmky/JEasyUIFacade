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

    private $on_open_script = '';

    protected function init()
    {
        parent::init();
        $this->setElementType('panel');
    }

    public function generateHtml()
    {
        $widget = $this->getWidget();
        
        $children_html = <<<HTML
        
                            {$this->buildHtmlForWidgets()}
                            <div id="{$this->getId()}_sizer" style="width:calc(100%/{$this->getNumberOfColumns()});min-width:{$this->getWidthMinimum()}px;"></div>
HTML;
        
        // Wrap children widgets with a grid for masonry layouting - but only if there is something to be layed out
        // Normalerweise wird das der masonry_grid-wrapper nicht gebraucht. Masonry ordnet
        // dann die Elemente an und passt direkt die Grosse des Panels an den neuen Inhalt an.
        // Nur wenn das Panel den gesamten Container ausfuellt, darf seine Groesse nicht
        // geaendert werden. In diesem Fall wird der wrapper eingefuegt und stattdessen seine
        // Groesse geaendert. Dadurch wird der Inhalt scrollbar im Panel angezeigt.
        
        if ((is_null($widget->getParent()) || (($containerWidget = $widget->getContainerWidget()) && ($containerWidget->countVisibleWidgets() == 1))) && ($widget->countVisibleWidgets() > 1)) {
            $children_html = <<<HTML

                        <div class="grid" id="{$this->getId()}_masonry_grid" style="width:100%;height:100%;">
                            {$children_html}
                        </div>
HTML;
        }
        
        // A standalone panel will always fill out the parent container (fit: true), but
        // other widgets based on a panel may not do so. Thus, the fit data-option is added
        // here, in the generate_html() method, which is verly likely to be overridden in
        // extending classes!
        
        // Wrapper wird gebraucht, denn es wird von easyui neben dem .easyui-panel div
        // ein .panel-header div erzeugt, welches sonst von masonry nicht beachtet wird
        // (beide divs .panel-header und .easyui-panel/.panel-body werden unter einem
        // .panel div zusammengefasst).
        // Fit:true wird gebraucht, denn sonst aendert das Panel seine Groesse nicht mehr
        // wenn sich die Groesse des Bildschirms/Containers aendert.
        $output = <<<HTML

                <div class="fitem {$this->getMasonryItemClass()}" style="width:{$this->getWidth()};min-width:{$this->getMinWidth()};height:{$this->getHeight()};padding:{$this->getPadding()};box-sizing:border-box;">
                    <div class="easyui-{$this->getElementType()}"
                            id="{$this->getId()}"
                            data-options="{$this->buildJsDataOptions()},fit:true"
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
            $this->addOnLoadScript($this->buildJsLayouter() . ';');
            $this->addOnResizeScript($this->buildJsLayouter() . ';');
            //$this->addOnOpenScript($this->buildJsLayouter() . ';');
        }
        $collapsibleScript = 'collapsible: ' . ($widget->isCollapsible() ? 'true' : 'false');
        $iconClassScript = $widget->getIconName() ? ', iconCls:\'' . $this->buildCssIconClass($widget->getIconName()) . '\'' : '';
        $onLoadScript = $this->getOnLoadScript() ? ', onLoad: function(){' . $this->getOnLoadScript() . '}' : '';
        $onResizeScript = $this->getOnResizeScript() ? ', onResize: function(){' . $this->getOnResizeScript() . '}' : '';
        $onOpenScript = $this->getOnOpenScript() ? ', onOpen: function(){' . $this->getOnOpenScript() . '}' : '';
        
        return $collapsibleScript . $iconClassScript . $onLoadScript . $onResizeScript . $onOpenScript;
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

    public function getOnOpenScript()
    {
        return $this->on_open_script;
    }

    public function addOnOpenScript($value)
    {
        $this->on_open_script .= $value;
        return $this;
    }

    public function buildJsLayouter()
    {
        return $this->getId() . '_layouter()';
    }

    public function buildJsLayouterFunction()
    {
        $widget = $this->getWidget();
        
        // Auch das Layout des Containers wird erneuert nachdem das eigene Layout aktualisiert
        // wurde.
        $layoutWidgetScript = '';
        if ($layoutWidget = $widget->getLayoutWidget()) {
            $layoutWidgetScript = <<<JS
{$this->getTemplate()->getElement($layoutWidget)->getId()}_layouter();
JS;
        }
        
        if ((is_null($widget->getParent()) || (($containerWidget = $widget->getContainerWidget()) && ($containerWidget->countVisibleWidgets() == 1))) && ($widget->countVisibleWidgets() > 1)) {
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
        {$layoutWidgetScript}
    }
JS;
        } else {
            $output = <<<JS

    function {$this->getId()}_layouter() {
        if (!$("#{$this->getId()}").data("masonry")) {
            if ($("#{$this->getId()}").find(".{$this->getId()}_masonry_fitem").length > 0) {
                $("#{$this->getId()}").masonry({
                    columnWidth: "#{$this->getId()}_sizer",
                    itemSelector: ".{$this->getId()}_masonry_fitem"
                });
            }
        } else {
            $("#{$this->getId()}").masonry("reloadItems");
            $("#{$this->getId()}").masonry();
        }
        {$layoutWidgetScript}
    }
JS;
        }
        
        return $output;
    }
}
?>