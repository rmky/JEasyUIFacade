<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\AbstractAjaxTemplate\Template\Elements\JqueryFormTrait;
use exface\Core\Widgets\Form;

/**
 * The Form widget is just another panel in jEasyUI.
 * The HTML form cannot be used here, because form widgets can contain
 * tabs and the tabs implementation in jEasyUI is using HTML forms, so it does not work within a <form> element.
 *
 * @method Form get_widget()
 *        
 * @author Andrej Kabachnik
 *        
 */
class euiForm extends euiPanel
{
    
    use JqueryFormTrait;

    public function generateHtml()
    {
        return parent::generateHtml() . $this->buildHtmlFooter();
    }

    protected function buildHtmlFooter()
    {
        $output = '';
        if ($this->getWidget()->hasButtons()) {
            $output = <<<HTML

				<div id="{$this->getFooterId()}" style="padding:5px;">
					{$this->buildHtmlButtons()}
				</div>

HTML;
        }
        return $output;
    }

    protected function hasFooter()
    {
        if ($this->getWidget()->hasButtons()) {
            return true;
        }
        return false;
    }

    protected function getFooterId()
    {
        return $this->getId() . '_footer';
    }

    public function buildJsDataOptions()
    {
        $options = parent::buildJsDataOptions();
        
        if ($this->hasFooter()) {
            $options .= ", footer: '#" . $this->getFooterId() . "'";
        }
        
        return $options;
    }
}
?>