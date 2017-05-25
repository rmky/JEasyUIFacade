<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiText extends euiAbstractElement
{

    function generateHtml()
    {
        $output = '<p>' . $this->getWidget()->getText() . '</p>';
        return $output;
    }

    function generateJs()
    {
        return '';
    }
}
?>