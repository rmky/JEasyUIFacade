<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputDate extends euiInput
{

    protected function init()
    {
        parent::init();
        $this->setElementType('datebox');
    }

    function generateHtml()
    {
        /* @var $widget \exface\Core\Widgets\Input */
        $widget = $this->getWidget();
        
        $value = $this->escapeString($this->getValueWithDefaults());
        $requiredScript = $widget->isRequired() ? 'required="true" ' : '';
        $disabledScript = $widget->isDisabled() ? 'disabled="disabled" ' : '';
        
        $output = <<<HTML

                <input style="height: 100%; width: 100%;"
                    id="{$this->getId()}"
                    name="{$widget->getAttributeAlias()}"
                    value="{$value}"
                    {$requiredScript}
                    {$disabledScript} />
HTML;
        
        return $this->buildHtmlWrapperDiv($output);
    }

    function generateJs()
    {
        $output = <<<JS

    $("#{$this->getId()}").{$this->getElementType()}({
        {$this->buildJsDataOptions()}
    });
    
    {$this->buildJsDateParser()}
    {$this->buildJsDateFormatter()}
JS;
        
        return $output;
    }

    protected function buildJsDataOptions()
    {
        return <<<JS

        formatter: {$this->getId()}_dateFormatter,
        parser: {$this->getId()}_dateParser,
        onHidePanel: function() {
            // onHidePanel wird der Inhalt formatiert (beim Verlassen des Feldes), der ausge-
            // fuehrte Code entspricht dem beim Druecken der Enter-Taste
            // Known Issue: wird sehr schnell Enter oder Tab gedrueckt (bevor das Datum im
            // Kalender angezeigt wird), wird auf das vorherige Datum zurueckgesetzt 
            {$this->getId()}_jquery = $("#{$this->getId()}");
            currentDate = {$this->getId()}_jquery.datebox("calendar").calendar("options").current;
            if (currentDate) {
                {$this->getId()}_jquery.datebox("setValue", {$this->getId()}_dateFormatter(currentDate));
            }
        }
JS;
    }

    public function generateHeaders()
    {
        $headers = parent::generateHeaders();
        $headers[] = '<script type="text/javascript" src="exface/vendor/npm-asset/datejs/build/production/date.min.js"></script>';
        return $headers;
    }

    public function buildJsValueGetter()
    {
        return '$("#' . $this->getId() . '").data("_internalValue")';
    }

    protected function buildJsScreenDateFormat()
    {
        return 'dd.MM.yyyy';
    }

    protected function buildJsInternalDateFormat()
    {
        return 'yyyy-MM-dd';
    }

    protected function buildJsDateParser()
    {
        $output = <<<JS

    function {$this->getId()}_dateParser(date) {
        // date ist ein String und wird zu einem date-Objekt geparst
        
        // Variablen initialisieren
        var {$this->getId()}_jquery = $("#{$this->getId()}");
        var match = null;
        
        // dd.MM.yyyy, dd-MM-yyyy, dd/MM/yyyy, d.M.yyyy, d-M-yyyy, d/M/yyyy
        match = /(\d{1,2})([.\-/])(\d{1,2})([.\-/])(\d{4})/.exec(date);
        if (match != null) {
            var output = new Date(Number(match[5]), Number(match[3]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            {$this->getId()}_jquery.data("_screenFormat", "dd" + match[2] + "MM" + match[4] + "yyyy");
            return output;
        }
        // yyyy.MM.dd, yyyy-MM-dd, yyyy/MM/dd, yyyy.M.d, yyyy-M-d, yyyy/M/d
        match = /(\d{4})([.\-/])(\d{1,2})([.\-/])(\d{1,2})/.exec(date);
        if (match != null) {
            var output = new Date(Number(match[1]), Number(match[3]) - 1, Number(match[5]))
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            {$this->getId()}_jquery.data("_screenFormat", "yyyy" + match[2] + "MM" + match[4] + "dd");
            return output;
        }
        // dd.MM.yy, dd-MM-yy, dd/MM/yy, d.M.yy, d-M-yy, d/M/yy
        match = /(\d{1,2})([.\-/])(\d{1,2})([.\-/])(\d{2})/.exec(date);
        if (match != null) {
            var output = new Date(2000 + Number(match[5]), Number(match[3]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            {$this->getId()}_jquery.data("_screenFormat", "dd" + match[2] + "MM" + match[4] + "yyyy");
            return output;
        }
        // yy.MM.dd, yy-MM-dd, yy/MM/dd, yy.M.d, yy-M-d, yy/M/d
        match = /(\d{2})([.\-/])(\d{1,2})([.\-/])(\d{1,2})/.exec(date);
        if (match != null) {
            var output = new Date(2000 + Number(match[1]), Number(match[3]) - 1, Number(match[5]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            {$this->getId()}_jquery.data("_screenFormat", "yyyy" + match[2] + "MM" + match[4] + "dd");
            return output;
        }
        // dd.MM, dd-MM, dd/MM, d.M, d-M, d/M
        match = /(\d{1,2})([.\-/])(\d{1,2})/.exec(date);
        if (match != null) {
            var output = new Date((new Date()).getFullYear(), Number(match[3]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            {$this->getId()}_jquery.data("_screenFormat", "dd" + match[2] + "MM" + match[2] + "yyyy");
            return output;
        }
        // ddMMyyyy
        match = /^(\d{2})(\d{2})(\d{4})$/.exec(date);
        if (match != null) {
            var output = new Date(Number(match[3]), Number(match[2]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            {$this->getId()}_jquery.data("_screenFormat", "{$this->buildJsScreenDateFormat()}");
            return output;
        }
        // ddMMyy
        match = /^(\d{2})(\d{2})(\d{2})$/.exec(date);
        if (match != null) {
            var output = new Date(2000 + Number(match[3]), Number(match[2]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            {$this->getId()}_jquery.data("_screenFormat", "{$this->buildJsScreenDateFormat()}");
            return output;
        }
        // ddMM
        match = /^(\d{2})(\d{2})$/.exec(date);
        if (match != null) {
            var output = new Date((new Date()).getFullYear(), Number(match[2]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            {$this->getId()}_jquery.data("_screenFormat", "{$this->buildJsScreenDateFormat()}");
            return output;
        }
        // +/- ... T/D/W/M/J/Y
        match = /^([+\-]?\d+)([TtDdWwMmJjYy])$/.exec(date);
        if (match != null) {
            var output = Date.today();
            switch (match[2].toUpperCase()) {
                case "T":
                case "D":
                    output.addDays(Number(match[1]));
                    break;
                case "W":
                    output.addWeeks(Number(match[1]));
                    break;
                case "M":
                    output.addMonths(Number(match[1]));
                    break;
                case "J":
                case "Y":
                    output.addYears(Number(match[1]));
            }
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            {$this->getId()}_jquery.data("_screenFormat", "{$this->buildJsScreenDateFormat()}");
            return output;
        }
        // TODAY, HEUTE, NOW, JETZT, YESTERDAY, GESTERN, TOMORROW, MORGEN
        switch (date.toUpperCase()) {
            case "TODAY":
            case "HEUTE":
            case "NOW":
            case "JETZT":
                var output = Date.today();
                {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
                {$this->getId()}_jquery.data("_screenFormat", "{$this->buildJsScreenDateFormat()}");
                return output;
                break;
            case "YESTERDAY":
            case "GESTERN":
                var output = Date.today().addDays(-1);
                {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
                {$this->getId()}_jquery.data("_screenFormat", "{$this->buildJsScreenDateFormat()}");
                return output;
                break;
            case "TOMORROW":
            case "MORGEN":
                var output = Date.today().addDays(1);
                {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
                {$this->getId()}_jquery.data("_screenFormat", "{$this->buildJsScreenDateFormat()}");
                return output;
        }
        
        {$this->getId()}_jquery.data("_internalValue", "");
        {$this->getId()}_jquery.data("_screenFormat", "");
        return null;
    }
JS;
        
        return $output;
    }

    protected function buildJsDateFormatter()
    {
        $output = <<<JS

    function {$this->getId()}_dateFormatter(date) {
        // date ist ein date-Objekt und wird zu einem String geparst
        return date.toString($("#{$this->getId()}").data("_screenFormat") ? $("#{$this->getId()}").data("_screenFormat") : "{$this->buildJsScreenDateFormat()}");
    }
JS;
        
        return $output;
    }
}