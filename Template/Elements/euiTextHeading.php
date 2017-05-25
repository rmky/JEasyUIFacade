<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiTextHeading extends euiText
{

    function generateHtml()
    {
        $output = '';
        $output .= '<h' . $this->getWidget()->getHeadingLevel() . ' id="' . $this->getId() . '">' . $this->getWidget()->getText() . '</h' . $this->getWidget()->getHeadingLevel() . '>';
        return $output;
    }
}
?>