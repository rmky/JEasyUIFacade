<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Tabs;
use exface\Core\DataTypes\BooleanDataType;

/**
 *
 * @author Andrej Kabachnik
 *        
 * @method Tabs getWidget()
 *        
 */
class euiTabs extends euiContainer
{

    private $fit_option = true;
    
    private $style_as_pills = false;
    
    public function generateHtml()
    {
        $output = <<<HTML
	<div id="{$this->getId()}" class="easyui-tabs" data-options="{$this->buildJsDataOptions()}">
		{$this->buildHtmlForChildren()}
	</div>
HTML;
        return $output;
    }
    
    public function buildJsDataOptions()
    {
        $widget = $this->getWidget();
        
        return "border:false, tabPosition: '" . $widget->getTabPosition() . "'"
           . ($this->getFitOption() ? ", fit: true" : "")
           . ($this->getStyleAsPills() ? ", pill: true" : "")
           . ($widget->getTabPosition() == Tabs::TAB_POSITION_LEFT || $widget->getTabPosition() == Tabs::TAB_POSITION_RIGHT ? ', plain: true' : '')
           . ($widget->getHideTabsCaptions() ? ', headerWidth: 38' : '')
           ;
    }
    
    public function setFitOption($value)
    {
        $this->fit_option = BooleanDataType::parse($value);
        return $this;
    }
    
    public function getFitOption()
    {
        return $this->fit_option;
    }

    public function getStyleAsPills()
    {
        return $this->style_as_pills;
    }

    public function setStyleAsPills($style_as_pills)
    {
        $this->style_as_pills = BooleanDataType::parse($style_as_pills);
        return $this;
    }
 
    /**
     * Returns the default number of columns to layout this widget.
     *
     * @return integer
     */
    public function getDefaultColumnNumber()
    {
        return $this->getTemplate()->getConfig()->getOption("WIDGET.TABS.COLUMNS_BY_DEFAULT");
    }
    
}
?>