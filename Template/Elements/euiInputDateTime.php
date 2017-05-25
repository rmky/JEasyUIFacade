<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputDateTime extends euiInputDate
{

    protected function init()
    {
        parent::init();
        $this->setElementType('datetimebox');
    }

    protected function buildJsDateFormat()
    {
        return 'yyyy-MM-dd HH:mm:ss';
    }
}