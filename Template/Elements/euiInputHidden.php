<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputHidden extends euiInput
{

    protected function init()
    {
        parent::init();
        $this->setElementType('hidden');
    }

    function generateHtml()
    {
        $output = '<input type="hidden" 
								name="' . $this->getWidget()->getAttributeAlias() . '" 
								value="' . $this->getValueWithDefaults() . '" 
								id="' . $this->getId() . '" />';
        return $output;
    }

    function generateJs()
    {
        $output .= $this->buildJsLiveReference();
        return $output;
    }

    function buildJsValueSetterMethod($value)
    {
        return 'val(' . $value . ')';
    }

    function buildJsValueGetterMethod()
    {
        return 'val()';
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::buildJsValidator()
     */
    function buildJsValidator()
    {
        return 'true';
    }
}