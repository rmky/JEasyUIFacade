<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputGroup extends euiPanel
{
    protected function init(){
        parent::init();
        $this->addElementCssClass('exf-input-group');
    }
    
    public function buildJsDataOptions()
    {
        return parent::buildJsDataOptions() . ', border: false';
    }
}
?>