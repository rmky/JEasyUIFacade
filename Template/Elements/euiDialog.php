<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiDialog extends euiForm
{

    private $buttons_div_id = '';
    
    /**
     * 
     * {@inheritDoc}
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
     * {@inheritDoc}
     * @see \exface\JEasyUiTemplate\Template\Elements\euiForm::generateHtml()
     */
    public function generateHtml()
    {
        $contents = ($this->getWidget()->getLazyLoading() ? '' : $this->buildHtmlForWidgets());
        
        if (! $this->getWidget()->getHideHelpButton()) {
            $window_tools = '<a href="javascript:' . $this->getTemplate()->getElement($this->getWidget()->getHelpButton())->buildJsClickFunctionName() . '()" class="icon-help"></a>';
        }
        
        $dialog_title = str_replace('"', '\"', $this->getWidget()->getCaption());
        
        $output = <<<HTML
	<div class="easyui-dialog" id="{$this->getId()}" data-options="{$this->buildJsDataOptions()}" title="{$dialog_title}" style="width: {$this->getWidth()}; height: {$this->getHeight()};">
		{$contents}		
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
     * {@inheritDoc}
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
        $output = parent::buildJsDataOptions() . ($widget->isMaximizable() ? ', maximizable: true, maximized: ' . ($widget->isMaximized() ? 'true' : 'false') : '') . ", cache: false" . ", closed: false" . ", buttons: '#{$this->buttons_div_id}'" . ", tools: '#{$this->getId()}_window_tools'" . ", modal: true";
        return $output;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::getWidth()
     */
    public function getWidth()
    {
        if ($this->getWidget()->getWidth()->isUndefined()) {
            if (!is_null($this->getWidget()->getNumberOfColumns())){
                $number_of_columns = $this->getWidget()->getNumberOfColumns();
            } else {
                $number_of_columns = $this->getTemplate()->getConfig()->getOption('WIDGET.DIALOG.COLUMNS_BY_DEFAULT');
            }
            $this->getWidget()->setWidth(($number_of_columns * $this->getWidthRelativeUnit() + 35) . 'px');
        }
        return parent::getWidth();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::getHeight()
     */
    public function getHeight()
    {
        if ($this->getWidget()->getHeight()->isUndefined()) {
            $this->getWidget()->setHeight('80%');
        }
        return parent::getHeight();
    }
}
?>