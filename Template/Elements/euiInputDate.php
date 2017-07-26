<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputDate extends euiInput
{

    private $dateFormatScreen;

    private $dateFormatInternal;

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
            // fuehrte Code entspricht dem beim Druecken der Enter-Taste.
            // Known Issue: Wird sehr schnell Enter oder Tab gedrueckt (bevor das Datum im
            // Kalender angezeigt wird), so wird auf das vorherige Datum zurueckgesetzt. Da
            // beim Druecken der Enter-Taste das Panel auch geschlossen wird, wird der Code
            // dann zweimal ausgefuehrt (beim Schliessen des Panels ohne Enter einmal).
            {$this->getId()}_jquery = $("#{$this->getId()}");
            currentDate = {$this->getId()}_jquery.{$this->getElementType()}("calendar").calendar("options").current;
            if (currentDate) {
                {$this->getId()}_jquery.{$this->getElementType()}("setValue", {$this->getId()}_dateFormatter(currentDate));
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
    protected function getDateJsFileName()
    {
        // Es waere wuenschenswert die Formatierung des Datums abhaengig vom Locale zu machen.
        // Das Problem dabei ist folgendes: Wird im DateFormatter das Datum von DateJs ent-
        // sprechend dem Locale formatiert, so muss der DateParser kompatibel sein. Es kommt
        // sonst z.B. beim amerik. Format zu Problemen. Der 5.11.2015 wird als 11/5/2015
        // formatiert, dann aber entspr. den alexa RMS Formaten als 11.5.2015 geparst. Der
        // Parser von DateJs kommt leider nicht mit allen alexa RMS Formaten zurecht.
        
        // Eine Loesung waere fuer die verschiedenen Locales versch. eigene Parser zu
        // schreiben, dann koennte man aber auch gleich versch. eigene Formatter hinzufuegen.
        // In der jetzt umgesetzten Loesung wird das Anzeigeformat in den Uebersetzungsdateien
        // festgelegt. Dabei ist darauf zu achten, dass es kompatibel zum Parser ist, das
        // amerikanische Format MM/dd/yyyy ist deshalb nicht moeglich, da es vom Parser als
        // dd/MM/yyyy interpretiert wird.
        
        /*
         * $dateJsBasepath = MODX_BASE_PATH . 'exface' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'npm-asset' . DIRECTORY_SEPARATOR . 'datejs' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'production' . DIRECTORY_SEPARATOR;
         *
         * $locale = $this->getTemplate()->getApp()->getTranslator()->getLocale();
         * $filename = 'date-' . str_replace("_", "-", $locale) . '.min.js';
         * if (file_exists($dateJsBasepath . $filename)) {
         * return $filename;
         * }
         *
         * $fallbackLocales = $this->getTemplate()->getApp()->getTranslator()->getFallbackLocales();
         * foreach ($fallbackLocales as $fallbackLocale) {
         * $filename = 'date-' . str_replace("_", "-", $fallbackLocale) . '.min.js';
         * if (file_exists($dateJsBasepath . $filename)) {
         * return $filename;
         * }
         * }
         */
        return 'date.min.js';
    }

    public function buildJsValueGetter()
    {
        return '$("#' . $this->getId() . '").data("_internalValue")';
    }

    protected function buildJsScreenDateFormat()
    {
        if (is_null($this->dateFormatScreen)) {
            $this->dateFormatScreen = $this->translate("DATE.FORMAT.SCREEN");
        }
        return $this->dateFormatScreen;
    }

    protected function buildJsInternalDateFormat()
    {
        if (is_null($this->dateFormatInternal)) {
            $this->dateFormatInternal = $this->translate("DATE.FORMAT.INTERNAL");
        }
        return $this->dateFormatInternal;
    }

    protected function buildJsDateParser()
    {
        $output = <<<JS

    function {$this->getId()}_dateParser(date) {
        // date ist ein String und wird zu einem date-Objekt geparst
        
        // date wird entsprechend CultureInfo geparst, hierfuer muss das entsprechende locale
        // DateJs eingebunden werden und ein kompatibler Formatter verwendet werden
        //return Date.parse(date);
        
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
        
        // "d" entspricht CultureInfo shortDate Format Pattern, hierfuer muss das
        // entpsprechende locale DateJs eingebunden werden und ein kompatibler Parser ver-
        // wendet werden
        //return date.toString("d");
        
        // Das Format in dateFormatScreen muss mit dem DateParser kompatibel sein. Das
        // amerikanische Format MM/dd/yyyy wird vom Parser als dd/MM/yyyy interpretiert und
        // kann deshalb nicht verwendet werden. Loesung waere den Parser anzupassen.
        return date.toString("{$this->buildJsScreenDateFormat()}");
    }
JS;
        
        return $output;
    }
}