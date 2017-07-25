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
        $headers[] = '<script type="text/javascript" src="exface/vendor/npm-asset/datejs/build/production/' . $this->getDateJsFileName() . '"></script>';
        return $headers;
    }
    
    /**
     * Generates the DateJs filename based on the locale provided by the translator.
     * 
     * @return string
     */
    protected function getDateJsFileName() {
        $dateJsBasepath = MODX_BASE_PATH . 'exface' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'npm-asset' . DIRECTORY_SEPARATOR . 'datejs' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'production' . DIRECTORY_SEPARATOR;
        
        $locale = $this->getTemplate()->getApp()->getTranslator()->getLocale();
        $filename = 'date-' . str_replace("_", "-", $locale) . '.min.js';
        if (file_exists($dateJsBasepath . $filename)) {
            return $filename;
        }
        
        $fallbackLocales = $this->getTemplate()->getApp()->getTranslator()->getFallbackLocales();
        foreach ($fallbackLocales as $fallbackLocale) {
            $filename = 'date-' . str_replace("_", "-", $fallbackLocale) . '.min.js';
            if (file_exists($dateJsBasepath . $filename)) {
                return $filename;
            }
        }
        
        return 'date.min.js';
    }

    public function buildJsValueGetter()
    {
        return '$("#' . $this->getId() . '").data("_internalValue")';
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
        match = /(\d{1,2})[.\-/](\d{1,2})[.\-/](\d{4})/.exec(date);
        if (match != null) {
            var output = new Date(Number(match[3]), Number(match[2]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            return output;
        }
        // yyyy.MM.dd, yyyy-MM-dd, yyyy/MM/dd, yyyy.M.d, yyyy-M-d, yyyy/M/d
        match = /(\d{4})[.\-/](\d{1,2})[.\-/](\d{1,2})/.exec(date);
        if (match != null) {
            var output = new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]))
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            return output;
        }
        // dd.MM.yy, dd-MM-yy, dd/MM/yy, d.M.yy, d-M-yy, d/M/yy
        match = /(\d{1,2})[.\-/](\d{1,2})[.\-/](\d{2})/.exec(date);
        if (match != null) {
            var output = new Date(2000 + Number(match[3]), Number(match[2]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            return output;
        }
        // yy.MM.dd, yy-MM-dd, yy/MM/dd, yy.M.d, yy-M-d, yy/M/d
        match = /(\d{2})[.\-/](\d{1,2})[.\-/](\d{1,2})/.exec(date);
        if (match != null) {
            var output = new Date(2000 + Number(match[1]), Number(match[2]) - 1, Number(match[3]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            return output;
        }
        // dd.MM, dd-MM, dd/MM, d.M, d-M, d/M
        match = /(\d{1,2})[.\-/](\d{1,2})/.exec(date);
        if (match != null) {
            var output = new Date((new Date()).getFullYear(), Number(match[2]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            return output;
        }
        // ddMMyyyy
        match = /^(\d{2})(\d{2})(\d{4})$/.exec(date);
        if (match != null) {
            var output = new Date(Number(match[3]), Number(match[2]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            return output;
        }
        // ddMMyy
        match = /^(\d{2})(\d{2})(\d{2})$/.exec(date);
        if (match != null) {
            var output = new Date(2000 + Number(match[3]), Number(match[2]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
            return output;
        }
        // ddMM
        match = /^(\d{2})(\d{2})$/.exec(date);
        if (match != null) {
            var output = new Date((new Date()).getFullYear(), Number(match[2]) - 1, Number(match[1]));
            {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
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
                return output;
                break;
            case "YESTERDAY":
            case "GESTERN":
                var output = Date.today().addDays(-1);
                {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
                return output;
                break;
            case "TOMORROW":
            case "MORGEN":
                var output = Date.today().addDays(1);
                {$this->getId()}_jquery.data("_internalValue", output.toString("{$this->buildJsInternalDateFormat()}"));
                return output;
        }
        
        {$this->getId()}_jquery.data("_internalValue", "");
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
        // "d" entspricht CultureInfo shortDate Format Pattern
        return date.toString("d");
    }
JS;
        
        return $output;
    }
}