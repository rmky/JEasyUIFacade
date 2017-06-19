<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiDialog extends euiForm
{

    private $buttons_div_id = '';

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\euiPanel::init()
     */
    protected function init()
    {
        parent::init();
        $this->buttons_div_id = $this->getId() . '-buttons';
        $this->setElementType('dialog');
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\euiForm::generateHtml()
     */
    public function generateHtml()
    {
        $widget = $this->getWidget();
        
        $children_html = '';
        if (! $widget->getLazyLoading()) {
            $children_html = <<<HTML

            {$this->buildHtmlForWidgets()}
            <div id="{$this->getId()}_sizer" style="width:calc(100%/{$this->getNumberOfColumns()});min-width:{$this->getMinWidth()};"></div>
HTML;
            
            if ($widget->countVisibleWidgets() > 1) {
                // masonry_grid-wrapper wird benoetigt, da sonst die Groesse des Dialogs selbst
                // veraendert wird -> kein Scrollbalken.
                $children_html = <<<HTML

        <div class="grid" id="{$this->getId()}_masonry_grid" style="width:100%;height:100%;">
            {$children_html}
        </div>
HTML;
            }
        }
        
        if (! $this->getWidget()->getHideHelpButton()) {
            $window_tools = '<a href="javascript:' . $this->getTemplate()->getElement($this->getWidget()->getHelpButton())->buildJsClickFunctionName() . '()" class="icon-help"></a>';
        }
        
        $dialog_title = str_replace('"', '\"', $this->getWidget()->getCaption());
        
        $output = <<<HTML
	<div class="easyui-dialog" id="{$this->getId()}" data-options="{$this->buildJsDataOptions()}" title="{$dialog_title}" style="width: {$this->getWidth()}; height: {$this->getHeight()};">
		{$children_html}
	</div>
	<div id="{$this->buttons_div_id}">
		{$this->buildHtmlButtons()}
	</div>
	<div id="{$this->getId()}_window_tools">
		{$window_tools}
	</div>
HTML;
        return $output;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::generateJs()
     */
    public function generateJs()
    {
        $output = '';
        if (! $this->getWidget()->getLazyLoading()) {
            $output .= $this->buildJsForWidgets();
        }
        $output .= $this->buildJsButtons();
        
        // Add the help button in the bottom toolbar
        if (! $this->getWidget()->getHideHelpButton()) {
            $output .= $this->getTemplate()->generateJs($this->getWidget()->getHelpButton());
        }
        
        // Layout-Funktion hinzufuegen
        $output .= $this->buildJsLayouterFunction();
        
        return $output;
    }

    /**
     * Generates the contents of the data-options attribute (e.g.
     * iconCls, collapsible, etc.)
     *
     * @return string
     */
    public function buildJsDataOptions()
    {
        $this->addOnLoadScript("$('#" . $this->getId() . " .exf_input input').first().next().find('input').focus();");
        /* @var $widget \exface\Core\Widgets\Dialog */
        $widget = $this->getWidget();
        // TODO make the Dialog responsive as in http://www.jeasyui.com/demo/main/index.php?plugin=Dialog&theme=default&dir=ltr&pitem=
        // onOpen-Funktion wird hinzugefuegt, da sonst das Layout des Dialogs zu breit ist.
        $output = parent::buildJsDataOptions() . ($widget->isMaximizable() ? ', maximizable: true, maximized: ' . ($widget->isMaximized() ? 'true' : 'false') : '') . ", cache: false" . ", closed: false" . ", buttons: '#{$this->buttons_div_id}'" . ", tools: '#{$this->getId()}_window_tools'" . ", modal: true" . ", onOpen: function() {" . $this->buildJsLayouter() . ";}";
        return $output;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::getWidth()
     */
    public function getWidth()
    {
        if ($this->getWidget()->getWidth()->isUndefined()) {
            $number_of_columns = $this->getNumberOfColumns();
            $this->getWidget()->setWidth(($number_of_columns * $this->getWidthRelativeUnit() + 35) . 'px');
        }
        return parent::getWidth();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::getHeight()
     */
    public function getHeight()
    {
        if ($this->getWidget()->getHeight()->isUndefined()) {
            $this->getWidget()->setHeight('80%');
        }
        return parent::getHeight();
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