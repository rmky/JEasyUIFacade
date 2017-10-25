<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiText extends euiAbstractElement
{

    function generateHtml()
    {
        $output = '<p>' . nl2br($this->getWidget()->getText()) . '</p>';
        return $this->buildHtmlWrapper($output);
    }

    function generateJs()
    {
        return '';
    }
    
    public function getHeight()
    {
        if ($this->getWidget()->getHeight()->isUndefined()){
            return 'auto';
        } else {
            return parent::getHeight();
        }
    }
}
?>