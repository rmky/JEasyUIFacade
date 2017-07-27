<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\AbstractAjaxTemplate\Template\Elements\JqueryInputDateTrait;

// Es waere wuenschenswert die Formatierung des Datums abhaengig vom Locale zu machen.
// Das Problem dabei ist folgendes: Wird im DateFormatter das Datum von DateJs ent-
// sprechend dem Locale formatiert, so muss der DateParser kompatibel sein. Es kommt
// sonst z.B. beim amerikanischen Format zu Problemen. Der 5.11.2015 wird als 11/5/2015
// formatiert, dann aber entsprechend den alexa RMS Formaten als 11.5.2015 geparst. Der
// Parser von DateJs kommt hingegen leider nicht mit allen alexa RMS Formaten zurecht.

// Eine Loesung waere fuer die verschiedenen Locales verschiedene eigene Parser zu
// schreiben, dann koennte man aber auch gleich verschiedene eigene Formatter
// hinzufuegen.
// In der jetzt umgesetzten Loesung wird das Anzeigeformat in den Uebersetzungsdateien
// festgelegt. Dabei ist darauf zu achten, dass es kompatibel zum Parser ist, das
// amerikanische Format MM/dd/yyyy ist deshalb nicht moeglich, da es vom Parser als
// dd/MM/yyyy interpretiert wird.
class euiInputDate extends euiInput
{
    
    use JqueryInputDateTrait;

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
        // onHidePanel
        // Known Issue: Wird sehr schnell Enter oder Tab gedrueckt (bevor das Datum im
        // Kalender angezeigt wird), so wird auf das vorherige Datum zurueckgesetzt. Da
        // beim Druecken der Enter-Taste das Panel auch geschlossen wird, wird der Code
        // dann zweimal ausgefuehrt (beim Schliessen des Panels ohne Enter einmal).
        return <<<JS

        formatter: {$this->getId()}_dateFormatter,
        parser: {$this->getId()}_dateParser,
        onHidePanel: function() {
            // onHidePanel wird der Inhalt formatiert (beim Verlassen des Feldes), der
            // ausgefuehrte Code entspricht dem beim Druecken der Enter-Taste.
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
        $headers[] = '<script type="text/javascript" src="exface/vendor/npm-asset/datejs/build/production/' . $this->buildDateJsLocaleFilename() . '"></script>';
        return $headers;
    }

    public function buildJsValueGetter()
    {
        return '$("#' . $this->getId() . '").data("_internalValue")';
    }

    protected function buildJsDateFormatter()
    {
        // Das Format in dateFormatScreen muss mit dem DateParser kompatibel sein. Das
        // amerikanische Format MM/dd/yyyy wird vom Parser als dd/MM/yyyy interpretiert
        // und kann deshalb nicht verwendet werden. Loesung waere den Parser anzupassen.
        
        // Auch moeglich: Verwendung des DateJs-Formatters:
        // "d" entspricht CultureInfo shortDate Format Pattern, hierfuer muss das
        // entsprechende locale DateJs eingebunden werden und ein kompatibler Parser ver-
        // wendet werden
        // return date.toString("d");
        $output = <<<JS

    function {$this->getId()}_dateFormatter(date) {
        // date ist ein date-Objekt und wird zu einem String geparst
        return date.toString("{$this->buildJsDateFormatScreen()}");
    }
JS;
        
        return $output;
    }
}