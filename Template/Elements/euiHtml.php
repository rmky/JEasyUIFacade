<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiHtml extends euiText
{

    function init()
    {}

    function generateHtml()
    {
        $output = '';
        if ($this->getWidget()->getCss()) {
            $output .= '<style>' . $this->getWidget()->getCss() . '</style>';
        }
        if ($this->getWidget()->getCaption() && ! $this->getWidget()->getHideCaption()) {
            $output .= '<label for="' . $this->getId() . '">' . $this->getWidget()->getCaption() . '</label>';
        }
        
        $output .= '<div id="' . $this->getId() . '">' . $this->getWidget()->getHtml() . '</div>';
        return $output;
    }

    function generateJs()
    {
        return $this->getWidget()->getJavascript();
    }
}
?>