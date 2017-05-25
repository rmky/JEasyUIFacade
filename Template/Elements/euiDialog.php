<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiDialog extends euiForm
{

    private $buttons_div_id = '';

    protected function init()
    {
        parent::init();
        $this->buttons_div_id = $this->getId() . '-buttons';
        $this->setElementType('dialog');
    }

    function generateHtml()
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

    function generateJs()
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
        $this->addOnLoadScript("$('#" . $this->getId() . " .exf_input input').first().next().find('input').focus();");
        /* @var $widget \exface\Core\Widgets\Dialog */
        $widget = $this->getWidget();
        // TODO make the Dialog responsive as in http://www.jeasyui.com/demo/main/index.php?plugin=Dialog&theme=default&dir=ltr&pitem=
        $output = parent::buildJsDataOptions() . ($widget->getMaximizable() ? ', maximizable: true, maximized: ' . ($widget->getMaximized() ? 'true' : 'false') : '') . ", cache: false" . ", closed: false" . ", buttons: '#{$this->buttons_div_id}'" . ", tools: '#{$this->getId()}_window_tools'" . ", modal: true";
        return $output;
    }

    function getWidth()
    {
        if ($this->getWidget()->getWidth()->isUndefined()) {
            $this->getWidget()->setWidth((2 * $this->getWidthRelativeUnit() + 35) . 'px');
        }
        return parent::getWidth();
    }

    function getHeight()
    {
        if ($this->getWidget()->getHeight()->isUndefined()) {
            $this->getWidget()->setHeight('80%');
        }
        return parent::getHeight();
    }
}
?>