<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputDateTime extends euiInputDate
{

    protected function init()
    {
        parent::init();
        $this->setElementType('datetimebox');
    }

    protected function buildJsScreenDateFormat()
    {
        if (is_null($this->dateFormatScreen)) {
            $this->dateFormatScreen = $this->translate("DATETIME.FORMAT.SCREEN");
        }
        return $this->dateFormatScreen;
    }
    
    protected function buildJsInternalDateFormat()
    {
        if (is_null($this->dateFormatInternal)) {
            $this->dateFormatInternal = $this->translate("DATETIME.FORMAT.INTERNAL");
        }
        return $this->dateFormatInternal;
    }
    
    protected function buildJsDateParser()
    {
        // TODO: Muss angepasst werden um auch eingegebene Zeiten zu verarbeiten. Momentan
        // wird die Zeit ignoriert -> immer 00:00:00.
        return parent::buildJsDateParser();
    }
}