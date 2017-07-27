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
        // Vorsicht wenn neben dem Datum auch die Zeit uebergeben werden soll. In welcher
        // Zeitzone befindet sich der Client und der Server. In welcher Zeitzone erwartet
        // der Server die uebergebene Zeit? new Date(...) und date.toString arbeiten immer
        // mit der Zeitzone des Clients. Der Bootstrap Datepicker erwartet die
        // uebergebenen Dates in der UTC-Zeitzone und gibt auch entsprechende Dates
        // zurueck. Dates in UTC-Zeit koennen z.B. mit new Date(Date.UTC(yyyy, MM, dd))
        // erstellt werden.
        return parent::buildJsDateParser();
    }
}