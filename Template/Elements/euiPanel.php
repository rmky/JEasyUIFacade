<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Panel;

/**
 * The Panel widget is mapped to a panel in jEasyUI
 *
 * @author Andrej Kabachnik
 *        
 * @method Panel get_widget()
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
        $children_html = $this->buildHtmlForWidgets();
        
        // Wrap children widgets with a grid for masonry layouting - but only if there is something to be layed out
        if ($this->getWidget()->countWidgets() > 1) {
            $children_html = '<div class="grid">' . $children_html . '</div>';
        }
        
        // A standalone panel will always fill out the parent container (fit: true), but
        // other widgets based on a panel may not do so. Thus, the fit data-option is added
        // here, in the generate_html() method, which is verly likely to be overridden in
        // extending classes!
        $output = '
				<div class="easyui-' . $this->getElementType() . '" 
					id="' . $this->getId() . '"
					data-options="' . $this->buildJsDataOptions() . ', fit: true" 
					title="' . $this->getWidget()->getCaption() . '">
					' . $children_html . '
				</div>';
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
        /* @var $widget \exface\Core\Widgets\Panel */
        $widget = $this->getWidget();
        if ($widget->getColumnNumber() != 1) {
            $this->addOnLoadScript($this->buildJsLayouter());
            $this->addOnResizeScript($this->buildJsLayouter());
        }
        
        $output = "collapsible: " . ($widget->getCollapsible() ? 'true' : 'false') . ($widget->getIconName() ? ", iconCls:'" . $this->buildCssIconClass($widget->getIconName()) . "'" : '') . ($this->getOnLoadScript() ? ", onLoad: function(){" . $this->getOnLoadScript() . "}" : '') . ($this->getOnResizeScript() ? ", onResize: function(){" . $this->getOnResizeScript() . "}" : '');
        return $output;
    }

    public function generateHeaders()
    {
        $includes = parent::generateHeaders();
        if ($this->getWidget()->getColumnNumber() != 1) {
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
        $grid_jquery_selector = "$('#{$this->getId()} .grid')";
        $script .= <<<JS
	if (!$('#{$this->getId()} .grid').data('masonry')){
		if ({$grid_jquery_selector}.find('.fitem').length > 0){
			{$grid_jquery_selector}.masonry({itemSelector: '.fitem', columnWidth: {$this->getWidthRelativeUnit()}});
		}
	} else {
		{$grid_jquery_selector}.masonry('reloadItems');
		{$grid_jquery_selector}.masonry();
	}
JS;
        return $script;
    }
}
?>