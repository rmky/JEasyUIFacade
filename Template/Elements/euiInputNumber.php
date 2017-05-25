<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputNumber extends euiInput
{

    protected function init()
    {
        parent::init();
        $this->setElementType('numberbox');
    }

    protected function buildJsDataOptions()
    {
        $output = parent::buildJsDataOptions();
        if ($output) {
            $output .= ', ';
        }
        
        $output .= "precision: '" . $this->getWidget()->getPrecision() . "'
					, decimalSeparator: ','
				";
        return $output;
    }
}