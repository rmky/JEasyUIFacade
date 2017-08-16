<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputText extends euiInput
{

    protected function init()
    {
        parent::init();
        $this->setElementType('textbox');
        $this->setHeightDefault(2);
    }

    function generateHtml()
    {
        $output = ' <textarea 
							name="' . $this->getWidget()->getAttributeAlias() . '" 
							id="' . $this->getId() . '"  
							style="height: calc(100% - 6px); width: calc(100% - 6px);"
							' . ($this->getWidget()->isRequired() ? 'required="true" ' : '') . '
							' . ($this->getWidget()->isDisabled() ? 'disabled="disabled" ' : '') . '>' . $this->getWidget()->getValue() . '</textarea>
					';
        return $this->buildHtmlWrapperDiv($output);
        ;
    }

    function generateJs()
    {
        $output = '';
        $output .= $this->buildJsLiveReference();
        $output .= $this->buildJsOnChangeHandler();
        return $output;
    }

    public function buildJsValueSetterMethod($value)
    {
        return 'val(' . $value . ')';
    }

    /*
     * function buildJsDataOptions(){
     * return parent::buildJsDataOptions() . ', multiline: true';
     * }
     *
     * function buildJsValueSetterMethod($value){
     * return $this->getElementType() . '("setText", ' . $value . ')';
     * }
     */
    
    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::buildJsValidator()
     */
    function buildJsValidator()
    {
        return 'true';
    }
}