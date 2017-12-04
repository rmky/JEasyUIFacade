<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\ComboTable;
use exface\Core\Exceptions\Widgets\WidgetConfigurationError;
use exface\Core\Exceptions\InvalidArgumentException;

/**
 *
 * @method ComboTable getWidget()
 *        
 * @author Andrej Kabachnik
 *        
 */
class euiComboTable extends euiInput
{

    /**
     * Folgende privaten Variablen sind im data-Objekt des Elements gespeichert und wichtig
     * fuer die Funktion desselben:
     * _valueSetterUpdate ist gesetzt wenn der Wert des Objekts durch den Value-Setter
     * gesetzt wurde
     * _filterSetterUpdate ist gesetzt wenn sich eine Filter-Referenz geaendert hat
     * _clearFilterSetterUpdate ist gesetzt wenn das Objekt durch eine Filter-Referenz geleert
     * werden soll
     * _firstLoad ist nur beim ersten Laden gesetzt
     * _clear ist gesetzt wenn der Wert des Objekts geloescht wurde
     * _otherSuppressFilterSetterUpdate ist gesetzt wenn die durch eine Filter-Referenz abhaengigen
     * Objekte nicht aktualisiert werden sollen
     * _otherSupressLazyLoadingGroupUpdate ist gesetzt wenn die Objekte der gleichen LazyLoadingGroup
     * nicht aktualisiert werden sollen
     * _otherClearFilterSetterUpdate ist gesetzt wenn die durch eine Filter-Referenz abhaengigen
     * Objekte geleert werden sollen
     * _otherSuppressAllUpdates ist gesetzt wenn alle abhaengigen Objekte (durch Filter- oder
     * Value-Referenz) nicht aktualisiert werden sollen
     * _suppressReloadOnSelect ist gesetzt wenn nach dem selektieren eines Eintrags nicht
     * neu geladen werden soll (bei autoselectsinglesuggestion)
     * _currentText der seit der letzten gueltigen Auswahl eingegebene Text
     * _lastValidValue der letzte gueltige Wert des Objekts
     * _lastFilterSet die beim letzten Laden gesetzten Filter
     * _resultSetChanged ist gesetzt wenn die geladenen Daten veraendert wurden
     */
    private $js_debug_level = 0;

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\euiInput::init()
     */
    protected function init()
    {
        parent::init();
        $this->setElementType('combogrid');
        $this->setJsDebugLevel($this->getTemplate()->getConfig()->getOption("JAVASCRIPT_DEBUG_LEVEL"));
        
        // Register onChange-Handler for Filters with Live-Reference-Values
        $widget = $this->getWidget();
        if ($widget->getTable()->hasFilters()) {
            foreach ($widget->getTable()->getFilters() as $fltr) {
                if ($link = $fltr->getValueWidgetLink()) {
                    $linked_element = $this->getTemplate()->getElementByWidgetId($link->getWidgetId(), $widget->getPage());
                    
                    $widget_lazy_loading_group_id = $widget->getLazyLoadingGroupId();
                    $linked_element_lazy_loading_group_id = method_exists($linked_element->getWidget(), 'getLazyLoadingGroupId') ? $linked_element->getWidget()->getLazyLoadingGroupId() : '';
                    // Gehoert das Widget einer Lazyloadinggruppe an, so darf es keine Filterreferenzen
                    // zu Widgets außerhalb dieser Gruppe haben.
                    if ($widget_lazy_loading_group_id && ($linked_element_lazy_loading_group_id != $widget_lazy_loading_group_id)) {
                        throw new WidgetConfigurationError($widget, 'Widget "' . $widget->getId() . '" in lazy-loading-group "' . $widget_lazy_loading_group_id . '" has a filter-reference to widget "' . $linked_element->getWidget()->getId() . '" in lazy-loading-group "' . $linked_element_lazy_loading_group_id . '". Filter-references to widgets outside the own lazy-loading-group are not allowed.', '6V6C2HY');
                    }
                    
                    $on_change_script = <<<JS

                        if (typeof suppressFilterSetterUpdate == "undefined" || !suppressFilterSetterUpdate) {
                            if (typeof clearFilterSetterUpdate == "undefined" || !clearFilterSetterUpdate) {
                                $("#{$this->getId()}").data("_filterSetterUpdate", true);
                            } else {
                                $("#{$this->getId()}").data("_clearFilterSetterUpdate", true);
                            }
                            $("#{$this->getId()}").combogrid("grid").datagrid("reload");
                        }
JS;
                    
                    if ($widget_lazy_loading_group_id) {
                        $on_change_script = <<<JS

                    if (typeof suppressLazyLoadingGroupUpdate == "undefined" || !suppressLazyLoadingGroupUpdate) {
                        {$on_change_script}
                    }
JS;
                    }
                    
                    $linked_element->addOnChangeScript($on_change_script);
                }
            }
        }
        
        // Register an onChange-Script on the element linked by a disable condition.
        $this->registerDisableConditionAtLinkedElement();
        
        // Make sure, the table in the combo has a smaller default page size than regular (big) tables
        // This makes combotables faster with large data sets.
        if (is_null($this->getWidget()->getTable()->getPaginatePageSize())) {
            $this->getWidget()->getTable()->setPaginatePageSize($this->getTemplate()->getConfig()->getOption('WIDGET.COMBOTABLE.PAGE_SIZE'));
        }
    }

    /**
     *
     * @throws WidgetConfigurationError
     * @return \exface\JEasyUiTemplate\Template\Elements\euiComboTable
     */
    protected function registerLiveReferenceAtLinkedElement()
    {
        $widget = $this->getWidget();
        
        if ($linked_element = $this->getLinkedTemplateElement()) {
            // Gehoert das Widget einer Lazyloadinggruppe an, so darf es keine Value-
            // Referenzen haben.
            $widget_lazy_loading_group_id = $widget->getLazyLoadingGroupId();
            if ($widget_lazy_loading_group_id) {
                throw new WidgetConfigurationError($widget, 'Widget "' . $widget->getId() . '" in lazy-loading-group "' . $widget_lazy_loading_group_id . '" has a value-reference to widget "' . $linked_element->getWidget()->getId() . '". Value-references to other widgets are not allowed.', '6V6C3AP');
            }
            
            $linked_element->addOnChangeScript($this->buildJsLiveReference());
        }
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\euiInput::generateHtml()
     */
    function generateHtml()
    {
        /* @var $widget \exface\Core\Widgets\ComboTable */
        $widget = $this->getWidget();
        
        $value = $this->getValueWithDefaults();
        $nameScript = $widget->getAttributeAlias() . ($widget->getMultiSelect() ? '[]' : '');
        $requiredScript = $widget->isRequired() ? 'required="true" ' : '';
        $disabledScript = $widget->isDisabled() ? 'disabled="disabled" ' : '';
        
        $output = <<<HTML

                <input style="height:100%;width:100%;"
                    id="{$this->getId()}" 
                    name="{$nameScript}" 
                    value="{$value}"
                    {$requiredScript}
                    {$disabledScript} />
HTML;
        
        return $this->buildHtmlLabelWrapper($output);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\euiInput::generateJs()
     */
    function generateJs()
    {
        $output = <<<JS

            // Globale Variablen initialisieren.
            {$this->buildJsInitFunction()}
            {$this->buildJsFunctionPrefix()}init();
            // Debug-Funktionen hinzufuegen.
            {$this->buildJsDebugDataToStringFunction()}
			
            $("#{$this->getId()}").combogrid({
                {$this->buildJsInitOptions()}
            });
            
JS;
        
        // Es werden JavaScript Value-Getter-/Setter- und OnChange-Funktionen fuer die ComboTable erzeugt,
        // um duplizierten Code zu vermeiden.
        $output .= <<<JS

            {$this->buildJsValueGetterFunction()}
            {$this->buildJsValueSetterFunction()}
            {$this->buildJsOnChangeFunction()}
            {$this->buildJsClearFunction()}
JS;
        
        // Es werden Dummy-Methoden fuer die Filter der DataTable hinter dieser ComboTable generiert. Diese
        // Funktionen werden nicht benoetigt, werden aber trotzdem vom verlinkten Element aufgerufen, da
        // dieses nicht entscheiden kann, ob das Filter-Input-Widget existiert oder nicht. Fuer diese Filter
        // existiert kein Input-Widget, daher existiert fuer sie weder HTML- noch JavaScript-Code und es
        // kommt sonst bei einem Aufruf der Funktion zu einem Fehler.
        if ($this->getWidget()->getTable()->hasFilters()) {
            foreach ($this->getWidget()->getTable()->getFilters() as $fltr) {
                $output .= <<<JS

            function {$this->getTemplate()->getElement($fltr->getInputWidget())->buildJsFunctionPrefix()}valueSetter(value){}
JS;
            }
        }
        
        // Initialize the disabled state of the widget if a disabled condition is set.
        $output .= $this->buildJsDisableConditionInitializer();
        
        // Add a clear icon to each combo grid - a small cross to the right, that resets the value
        // TODO The addClearBtn extension seems to break the setText method, so that it also sets the value. Perhaps we can find a better way some time
        // $output .= "$('#" . $this->getId() . "').combogrid('addClearBtn', 'icon-clear');";
        
        return $output;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\euiInput::buildJsInitOptions()
     */
    function buildJsInitOptions()
    {
        /* @var $widget \exface\Core\Widgets\ComboTable */
        $widget = $this->getWidget();
        /* @var $table \exface\JEasyUiTemplate\Template\Elements\DataTable */
        $table = $this->getTemplate()->getElement($widget->getTable());
        
        // Add explicitly specified values to every return data
        foreach ($widget->getSelectableOptions() as $key => $value) {
            if ($key === '' || $key === EXF_LOGICAL_NULL || is_null($key)){
                continue;
            }
            $table->addLoadFilterScript('data.rows.unshift({' . $widget->getTable()->getUidColumn()->getDataColumnName() . ': "' . $key . '", ' . $widget->getTextColumn()->getDataColumnName() . ': "' . $value . '"});');
        }
        
        // Init the combogrid itself
        $inheritedOptions = '';
        if ($widget->getLazyLoading() || (! $widget->getLazyLoading() && $widget->isDisabled())) {
            $inheritedOptions = $table->buildJsDataSource();
        }
        $table->setOnBeforeLoad($this->buildJsOnBeforeload());
        $table->addOnLoadSuccess($this->buildJsOnLoadSucess());
        $table->addOnLoadError($this->buildJsOnLoadError());
        
        $inheritedOptions .= $table->buildJsInitOptionsHead();
        $inheritedOptions = trim($inheritedOptions, "\r\n\t,");
        
        $requiredScript = $widget->isRequired() ? ', required:true' : '';
        $disabledScript = $widget->isDisabled() ? ', disabled:true' : '';
        $multiSelectScript = $widget->getMultiSelect() ? ', multiple: true' : '';
        
        $output .= $inheritedOptions . <<<JS

                        , textField:"{$this->getWidget()->getTextColumn()->getDataColumnName()}"
                        , mode: "remote"
                        , method: "post"
                        , delay: 600
                        , panelWidth:600
                        {$requiredScript}
                        {$disabledScript}
                        {$multiSelectScript}
                        , onChange: function(newValue, oldValue) {
                            // Wird getriggert durch manuelle Eingabe oder durch setValue().
                            {$this->buildJsDebugMessage('onChange')}
                            var {$this->getId()}_jquery = $("#{$this->getId()}");
                            
                            function getValueArray(value) {
                                var valueArray;
                                switch ($.type(value)) {
                                    case "number":
                                        valueArray = [value]; break;
                                    case "string":
                                        if (value) {
                                            valueArray = $.map(value.split(","), $.trim); break;
                                        } else {
                                            valueArray = []; break;
                                        }
                                    case "array":
                                        valueArray = value; break;
                                    default:
                                        valueArray = [];
                                }
                                return valueArray;
                            }
                            
                            // newValue kann eine Number/String sein oder ein Array (bei multi_select).
                            var newValueArray = getValueArray(newValue);
                            var oldValueArray = getValueArray(oldValue);
                            
                            // Akualisieren von currentText. Es gibt keine andere gute Moeglichkeit
                            // an den gerade eingegebenen Text zu kommen (combogrid("getText") liefert
                            // keinen aktuellen Wert). Funktion dieses Wertes siehe onHidePanel.
                            {$this->getId()}_jquery.data("_currentText", newValueArray.join());
                            if (newValueArray.length == 0) {
                                //{$this->getId()}_jquery.data("_lastValidValue", "");
                                // Das Objekt hatte einen Wert, der geloescht wurde.
                                {$this->getId()}_jquery.data("_clear", true);
                                if ({$this->getId()}_jquery.data("_inLazyLoadingGroup")) {
                                    // Der eigene Wert wird geloescht.
                                    {$this->getId()}_jquery.data("_clearFilterSetterUpdate", true);
                                    // Loeschen der verlinkten Elemente wenn der Wert manuell geloescht wird.
                                    {$this->getId()}_jquery.data("_otherClearFilterSetterUpdate", true);
                                } else {
                                    // Die Updates der Filter-Links werden an dieser Stelle unterdrueckt und
                                    // nur einmal nach dem value-Setter update onLoadSuccess ausgefuehrt.
                                    {$this->getId()}_jquery.data("_suppressFilterSetterUpdate", true);
                                }
                                {$this->buildJsFunctionPrefix()}onChange();
                            }
                            // Anschließend an onChange wird neu geladen -> onBeforeLoad
                        }
                        , onSelect: function(index, row) {
                            // Wird getriggert durch manuelle Auswahl einer Zeile oder durch
                            // setSelection().
                            {$this->buildJsDebugMessage('onSelect')}
                            var {$this->getId()}_jquery = $("#{$this->getId()}");
                            
                            // Aktualisieren von currentSelections, loeschen von currentText. Funktion
                            // dieser Werte siehe onHidePanel.
                            var currentSelections = {$this->getId()}_jquery.data("_currentSelections");
                            if (currentSelections.indexOf(row) === -1) {
                                currentSelections.push(row);
                            }
                            {$this->getId()}_jquery.data("_currentText", "");
                            {$this->getId()}_jquery.combogrid("setText", "");
                            
                            // Wichtig fuer lazy_loading_groups, da sonst ein sich widersprechender Filter-
                            // satz hergestellt werden kann.
                            if ({$this->getId()}_jquery.data("_suppressReloadOnSelect")) {
                                // Verhindert das neu Laden onSelect, siehe onLoadSuccess (autoselectsinglesuggestion)
                                {$this->getId()}_jquery.removeData("_suppressReloadOnSelect");
                            } else if ({$this->getId()}_jquery.data("_inLazyLoadingGroup")) {
                                // Update des eigenen Widgets.
                                {$this->getId()}_jquery.data("_filterSetterUpdate", true);
                                {$this->getId()}_jquery.combogrid("grid").datagrid("reload");
                            }
                            
                            //Referenzen werden aktualisiert.
                            {$this->buildJsFunctionPrefix()}onChange();
                        }
                        , onCheck: function(index, row) {
                            var {$this->getId()}_jquery = $("#{$this->getId()}");
                            
                            {$this->getId()}_jquery.combogrid("setText", "");
                        }
                        , onUnselect: function(index, row) {
                            var {$this->getId()}_jquery = $("#{$this->getId()}");
                            
                            var currentSelections = {$this->getId()}_jquery.data("_currentSelections");
                            var rowIndex = currentSelections.indexOf(row);
                            if (rowIndex > -1) {
                                currentSelections.splice(rowIndex, 1);
                            }
                        }
                        , onSelectAll: function(rows) {
                            var {$this->getId()}_jquery = $("#{$this->getId()}");
                            
                            var currentSelections = {$this->getId()}_jquery.data("_currentSelections");
                            for (i = 0; i < rows.length; i++) {
                                if (currentSelections.indexOf(rows[i]) === -1) {
                                    currentSelections.push(rows[i]);
                                }
                            }
                            {$this->getId()}_jquery.combogrid("setText", "");
                        }
                        , onUnselectAll: function(rows) {
                            var {$this->getId()}_jquery = $("#{$this->getId()}");
                            
                            var currentSelections = {$this->getId()}_jquery.data("_currentSelections");
                            for (i = 0; i < rows.length; i++) {
                                var rowIndex = currentSelections.indexOf(rows[i]);
                                if (rowIndex > -1) {
                                    currentSelections.splice(rowIndex, 1);
                                }
                            }
                        }
                        , onShowPanel: function() {
                            // Wird firstLoad verhindert, wuerde man eine leere Tabelle sehen. Um das zu
                            // verhindern wird die Tabelle hier neu geladen, falls sie leer ist.
                            // Update: Wird immer noch doppelt geladen, wenn anfangs eine manuelle Eingabe
                            // gemacht wird -> onChange (-> Laden), onShowPanel (-> Laden)
                            {$this->buildJsDebugMessage('onShowPanel')}
                            var {$this->getId()}_jquery = $("#{$this->getId()}");
                            
                            if ({$this->getId()}_jquery.data("_firstLoad")) {
                                {$this->getId()}_jquery.combogrid("grid").datagrid("reload");
                            }
                        }
                        , onHidePanel: function() {
                            {$this->buildJsDebugMessage('onHidePanel')}
                            var {$this->getId()}_jquery = $("#{$this->getId()}");
                            
                            var selectedRows = {$this->getId()}_jquery.combogrid("grid").datagrid("getSelections");
                            // lastValidValue enthaelt den letzten validen Wert der ComboTable.
                            var lastValidValue = {$this->getId()}_jquery.data("_lastValidValue");
                            var currentValue = {$this->getId()}_jquery.combogrid("getValues").join();
                            // currentText enthaelt den seit der letzten validen Auswahl in die ComboTable eingegebenen Text,
                            // d.h. ist currentText nicht leer wurde Text eingegeben aber noch keine Auswahl getroffen.
                            var currentText = {$this->getId()}_jquery.data("_currentText");
                            
                            // Das Panel wird automatisch versteckt, wenn man das Eingabefeld verlaesst.
                            // Wurde zu diesem Zeitpunkt seit der letzten Auswahl Text eingegeben, aber
                            // kein Eintrag ausgewaehlt, dann wird der letzte valide Zustand wiederher-
                            // gestellt.
                            if (selectedRows.length == 0 && currentText) {
                                if (lastValidValue){
                                    {$this->getId()}_jquery.data("_currentText", "");
                                    {$this->buildJsFunctionPrefix()}valueSetter(lastValidValue);
                                } else {
                                    {$this->getId()}_jquery.data("_currentText", "");
                                    {$this->buildJsFunctionPrefix()}clear(true);
                                    if (currentValue != lastValidValue) {
                                        {$this->getId()}_jquery.combogrid("grid").datagrid("reload");
                                    }
                                }
                            }
                        }
JS;
        
        return $output;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::buildJsValueGetter()
     */
    function buildJsValueGetter($column = null, $row = null)
    {
        $params = $column ? '"' . $column . '"' : '';
        $params = $row ? ($params ? $params . ', ' . $row : $row) : $params;
        return $this->buildJsFunctionPrefix() . 'valueGetter(' . $params . ')';
    }

    /**
     * Creates a JavaScript function which returns the value of the element.
     *
     * @return string
     */
    function buildJsValueGetterFunction()
    {
        $output = <<<JS

                function {$this->buildJsFunctionPrefix()}valueGetter(column, row){
                    {$this->buildJsDebugMessage('valueGetter()')}
                    var {$this->getId()}_jquery = $("#{$this->getId()}");
                    
                    // Wird kein Spaltenname uebergeben, wird die UID-Spalte zurueckgegeben.
                    if (!column) {
                        column = {$this->getId()}_jquery.data("_uidColumnName");
                    }
                    
                    var selectedRows = {$this->getId()}_jquery.data("_currentSelections");
                    if (selectedRows.length > 0) {
                        if ({$this->getId()}_jquery.data("_multiSelect")) {
                            var resultArray = [];
                            for (i = 0; i < selectedRows.length; i++) {
                                // Wird die Spalte vom Server angefordert, das Attribut des Objekts existiert
                                // aber nicht, wird false zurueckgegeben (Booleans werden als "0"/"1" zurueckgegeben).
                                if (selectedRows[i][column] === undefined || selectedRows[i][column] === false) {
                                    if (window.console) { console.warn("The non-existing column \"" + column + "\" was requested from element \"{$this->getId()}\""); }
                                    resultArray.push("");
                                } else {
                                    resultArray.push(selectedRows[i][column]);
                                }
                            }
                            return resultArray.join();
                        } else {
                            if (selectedRows[0][column] === undefined || selectedRows[0][column] === false) {
                                if (window.console) { console.warn("The non-existing column \"" + column + "\" was requested from element \"{$this->getId()}\""); }
                                return "";
                            } else {
                                return selectedRows[0][column];
                            }
                        }
                    } else {
                        return "";
                    }
                }
                
JS;
        
        return $output;
    }

    /**
     * The JS value setter for EasyUI combogrids is a custom function defined in euiComboTable::generateJs() - it only needs to be called here.
     *
     * {@inheritdoc}
     *
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::buildJsValueSetter($value)
     */
    function buildJsValueSetter($value)
    {
        return $this->buildJsFunctionPrefix() . 'valueSetter(' . $value . ')';
    }

    /**
     * Creates a JavaScript function which sets the value of the element.
     *
     * @return string
     */
    function buildJsValueSetterFunction()
    {
        $output = <<<JS

                function {$this->buildJsFunctionPrefix()}valueSetter(value){
                    {$this->buildJsDebugMessage('valueSetter()')}
                    var {$this->getId()}_jquery = $("#{$this->getId()}");
                    
                    var valueArray;
                    if (value) {
                        switch ($.type(value)) {
                            case "number":
                                valueArray = [value]; break;
                            case "string":
                                valueArray = $.map(value.split(","), $.trim); break;
                            case "array":
                                valueArray = value; break;
                            default:
                                valueArray = [];
                        }
                    } else {
                        valueArray = [];
                    }
                    
                    if (valueArray.length > 0) {
                        var newSelections = [];
                        for (i = 0; i < valueArray.length; i++) {
                            var newSelection = {};
                            newSelection[{$this->getId()}_jquery.data("_uidColumnName")] = valueArray[i];
                            newSelections.push(newSelection);
                        }
                    }
                    
                    if ({$this->getId()}_jquery.data("_multiSelect")) {
                        {$this->getId()}_jquery.data("_currentSelections", newSelections);
                    } else {
                        if (newSelections.length <= 1) {
                            {$this->getId()}_jquery.data("_currentSelections", newSelections);
                        } else {
                            {$this->getId()}_jquery.data("_currentSelections", [newSelections[0]]);
                        }
                    }
                    
                    {$this->getId()}_jquery.data("_valueSetterUpdate", true);
                    {$this->getId()}_jquery.combogrid("grid").datagrid("reload");
                }
                
JS;
        
        return $output;
    }

    /**
     * Creates a JavaScript function which sets the value of the element.
     *
     * @return string
     */
    function buildJsOnChangeFunction()
    {
        $widget = $this->getWidget();
        
        $output = <<<JS

                function {$this->buildJsFunctionPrefix()}onChange(){
                    {$this->buildJsDebugMessage('onChange()')}
                    var {$this->getId()}_jquery = $("#{$this->getId()}");
                    
                    // Diese Werte koennen gesetzt werden damit, wenn der Wert der ComboTable
                    // geaendert wird, nur ein Teil oder gar keine verlinkten Elemente aktualisiert
                    // werden.
                    var suppressFilterSetterUpdate = false, clearFilterSetterUpdate = false, suppressAllUpdates = false, suppressLazyLoadingGroupUpdate = false;
                    if ({$this->getId()}_jquery.data("_otherSuppressFilterSetterUpdate")){
                        // Es werden keine Filter-Links aktualisiert.
                        {$this->getId()}_jquery.removeData("_otherSuppressFilterSetterUpdate");
                        suppressFilterSetterUpdate = true;
                    }
                    if ({$this->getId()}_jquery.data("_otherClearFilterSetterUpdate")){
                        // Filter-Links werden geleert.
                        {$this->getId()}_jquery.removeData("_otherClearFilterSetterUpdate");
                        clearFilterSetterUpdate = true;
                    }
                    if ({$this->getId()}_jquery.data("_otherSuppressAllUpdates")){
                        // Weder Werte-Links noch Filter-Links werden aktualisiert.
                        {$this->getId()}_jquery.removeData("_otherSuppressAllUpdates");
                        suppressAllUpdates = true;
                    }
                    if ({$this->getId()}_jquery.data("_otherSuppressLazyLoadingGroupUpdate")){
                        // Die LazyLoadingGroup wird nicht aktualisiert.
                        {$this->getId()}_jquery.removeData("_otherSuppressLazyLoadingGroupUpdate");
                        suppressLazyLoadingGroupUpdate = true;
                    }
                    
                    if (!suppressAllUpdates) {
                        {$this->getOnChangeScript()}
                    }
                }
                
JS;
        return $output;
    }

    /**
     * Creates the JavaScript-Code which is executed before loading the autosuggest-
     * data.
     * If a value was set programmatically a single filter for this value is
     * added to the request to display the label properly. Otherwise the filters
     * which were defined on the widget are added to the request. The filters are
     * removed after loading as their values can change because of live-references.
     *
     * @return string
     */
    function buildJsOnBeforeload()
    {
        $widget = $this->getWidget();
        
        // If the value is set data is loaded from the backend. Same if also value-text is set, because otherwise
        // live-references don't work at the beginning. If no value is set, loading from the backend is prevented.
        // The trouble here is, that if the first loading is prevented, the next time the user clicks on the dropdown button,
        // an empty table will be shown, because the last result is cached. To fix this, we bind a reload of the table to
        // onShowPanel in case the grid is empty (see above).
        if (! is_null($this->getValueWithDefaults()) && $this->getValueWithDefaults() !== '') {
            if (trim($widget->getValueText())) {
                // If the text is already known, set it and prevent initial backend request
                $widget_value_text = str_replace('"', '\"', trim($widget->getValueText()));
                $first_load_script = <<<JS

                        {$this->getId()}_jquery.combogrid("setText", "{$widget_value_text}");
                        {$this->getId()}_jquery.data("_lastValidValue", "{$this->getValueWithDefaults()}");
                        {$this->getId()}_jquery.data("_currentText", "");
                        return false;
JS;
            } else {
                $first_load_script = <<<JS

                        {$this->getId()}_jquery.data("_lastValidValue", "{$this->getValueWithDefaults()}");
                        {$this->getId()}_jquery.data("_currentText", "");
                        {$this->getId()}_jquery.data("_valueSetterUpdate", true);
                        currentFilterSet.fltr01_{$widget->getValueColumn()->getDataColumnName()} = "{$this->getValueWithDefaults()}";
JS;
            }
        } else {
            // If no value set, just supress initial autoload
            $first_load_script = <<<JS

                        {$this->getId()}_jquery.data("_lastValidValue", "");
                        {$this->getId()}_jquery.data("_currentText", "");
                        return false;
JS;
        }
        
        $fltrId = 0;
        // Add filters from widget
        $filters = [];
        if ($widget->getTable()->hasFilters()) {
            foreach ($widget->getTable()->getFilters() as $fltr) {
                if ($link = $fltr->getValueWidgetLink()) {
                    // filter is a live reference
                    $linked_element = $this->getTemplate()->getElementByWidgetId($link->getWidgetId(), $widget->getPage());
                    $filters[] = 'currentFilterSet.fltr' . str_pad($fltrId ++, 2, 0, STR_PAD_LEFT) . '_' . urlencode($fltr->getAttributeAlias()) . ' = "' . $fltr->getComparator() . '"+' . $linked_element->buildJsValueGetter($link->getColumnId()) . ';';
                } else {
                    // filter has a static value
                    $filters[] = 'currentFilterSet.fltr' . str_pad($fltrId ++, 2, 0, STR_PAD_LEFT) . '_' . urlencode($fltr->getAttributeAlias()) . ' = "' . $fltr->getComparator() . urlencode(strpos($fltr->getValue(), '=') === 0 ? '' : $fltr->getValue()) . '";';
                }
            }
        }
        $filters_script = implode("\n                        ", $filters);
        // Beim Leeren eines Widgets in einer in einer lazy-loading-group wird kein Filter gesetzt,
        // denn alle Filter sollten leer sein (alle Elemente der Gruppe leer). Beim Leeren eines
        // Widgets ohne Gruppe werden die normalen Filter gesetzt.
        $clear_filters_script = $widget->getLazyLoadingGroupId() ? '' : $filters_script;
        // Add value filter (to show proper label for a set value)
        $value_filters = [];
        $value_filters[] = 'currentFilterSet.fltr' . str_pad($fltrId ++, 2, 0, STR_PAD_LEFT) . '_' . $widget->getValueColumn()->getDataColumnName() . ' = ' . $this->getId() . '_jquery.combogrid("getValues").join();';
        $value_filters_script = implode("\n                        ", $value_filters);
        
        // firstLoadScript: enthaelt Anweisungen, die nur beim ersten Laden ausgefuehrt
        // werden sollen (Initialisierung)
        // filters_script: fuegt die gesetzten Filter zur Anfrage hinzu
        // value_filters_script: fuegt einen Filter zur Anfrage hinzu, welcher auf dem
        // aktuell gesetzten Wert beruht
        // clear_filters_script: fuegt Filter zur Anfrage hinzu, welche beim Leeren des
        // Objekts gelten sollen
        
        $output = <<<JS

                    {$this->buildJsDebugMessage('onBeforeLoad')}
                    var {$this->getId()}_jquery = $("#{$this->getId()}");
                    
                    // Wird eine Eingabe gemacht, dann aber keine Auswahl getroffen, ist bei der naechsten
                    // Anfrage param.q noch gesetzt (param eigentlich nur Kopie???). Deshalb hier loeschen.
                    delete param.q;
                    
                    if (!{$this->getId()}_jquery.data("_lastFilterSet")) { {$this->getId()}_jquery.data("_lastFilterSet", {}); }
                    var currentFilterSet = {page: param.page, rows: param.rows};
                    
                    if ({$this->getId()}_jquery.data("_firstLoad") == undefined){
                        {$this->getId()}_jquery.data("_firstLoad", true);
                    } else if ({$this->getId()}_jquery.data("_firstLoad")){
                        {$this->getId()}_jquery.data("_firstLoad", false);
                    }
                    
                    if ({$this->getId()}_jquery.data("_valueSetterUpdate")) {
                        param._valueSetterUpdate = true;
                        {$value_filters_script}
                    } else if ({$this->getId()}_jquery.data("_clearFilterSetterUpdate")) {
                        param._clearFilterSetterUpdate = true;
                        {$clear_filters_script}
                    } else if ({$this->getId()}_jquery.data("_filterSetterUpdate")) {
                        param._filterSetterUpdate = true;
                        {$filters_script}
                        {$value_filters_script}
                    } else if ({$this->getId()}_jquery.data("_firstLoad")) {
                        param._firstLoad = true;
                        {$first_load_script}
                    } else {
                        currentFilterSet.q = {$this->getId()}_jquery.data("_currentText");
                        {$filters_script}
                    }
                    
                    // Die Filter der gegenwaertigen Anfrage werden mit den Filtern der letzten Anfrage
                    // verglichen. Sind sie identisch und wurden die zuletzt geladenen Daten nicht ver-
                    // aendert, wird die Anfrage unterbunden, denn das Resultat waere das gleiche.
                    if ((JSON.stringify(currentFilterSet) === JSON.stringify({$this->getId()}_jquery.data("_lastFilterSet"))) &&
                            !({$this->getId()}_jquery.data("_resultSetChanged"))) {
                        // Suchart entfernen, sonst ist sie beim naechsten Mal noch gesetzt
                        {$this->getId()}_jquery.removeData("_valueSetterUpdate");
                        {$this->getId()}_jquery.removeData("_clearFilterSetterUpdate");
                        {$this->getId()}_jquery.removeData("_filterSetterUpdate");
                        {$this->getId()}_jquery.removeData("_clear");
                        
                        return false;
                    } else {
                        {$this->getId()}_jquery.data("_lastFilterSet", currentFilterSet);
                        $.extend(param, currentFilterSet);
                    }
JS;
        
        return $output;
    }

    /**
     * Creates javascript-code which is executed after the successful loading of auto-
     * suggest-data.
     * If autoselect_single_suggestion is true, a single return value
     * from autosuggest is automatically selected.
     *
     * @return string
     */
    function buildJsOnLoadSucess()
    {
        $widget = $this->getWidget();
        
        $uidColumnName = $widget->getTable()->getUidColumn()->getDataColumnName();
        $textColumnName = $widget->getTextColumn()->getDataColumnName();
        
        $suppressLazyLoadingGroupUpdateScript = $widget->getLazyLoadingGroupId() ? '
                                // Ist das Widget in einer lazy-loading-group, werden keine Filter-Referenzen aktualisiert,
                                // denn alle Elemente der Gruppe werden vom Orginalobjekt bedient.
                                if (suppressLazyLoadingGroupUpdate) {
                                    ' . $this->getId() . '_jquery.data("_otherSuppressLazyLoadingGroupUpdate", true);
                                }' : '';
        
        $output = <<<JS

                    {$this->buildJsDebugMessage('onLoadSuccess')}
                    var {$this->getId()}_jquery = $("#{$this->getId()}");
                    var {$this->getId()}_datagrid = {$this->getId()}_jquery.combogrid("grid");
                    
                    var suppressAutoSelectSingleSuggestion = false;
                    var suppressLazyLoadingGroupUpdate = false;
                    
                    if ({$this->getId()}_jquery.data("_valueSetterUpdate")) {
                        // Update durch eine value-Referenz.
                        
                        {$this->getId()}_jquery.removeData("_valueSetterUpdate");
                        {$this->getId()}_jquery.removeData("_clearFilterSetterUpdate");
                        {$this->getId()}_jquery.removeData("_filterSetterUpdate");
                        {$this->getId()}_jquery.removeData("_clear");
                        
                        // Nach einem Value-Setter-Update wird der Text neu gesetzt um das Label ordentlich
                        // anzuzeigen und das onChange-Skript wird ausgefuehrt.
                        var selectedRows = {$this->getId()}_datagrid.datagrid("getSelections");
                        if (selectedRows.length > 0) {
                            {$this->getId()}_jquery.combogrid("setText", {$this->buildJsFunctionPrefix()}valueGetter("{$textColumnName}"));
                        }
                        
                        {$this->buildJsFunctionPrefix()}onChange();
                    } else if ({$this->getId()}_jquery.data("_clearFilterSetterUpdate")) {
                        // Leeren durch eine filter-Referenz.
                        
                        {$this->getId()}_jquery.removeData("_valueSetterUpdate");
                        {$this->getId()}_jquery.removeData("_clearFilterSetterUpdate");
                        {$this->getId()}_jquery.removeData("_filterSetterUpdate");
                        {$this->getId()}_jquery.removeData("_clear");
                        
                        {$this->buildJsFunctionPrefix()}clear(false);
                        
                        // Neu geladen werden muss nicht, denn die Filter waren beim vorangegangenen Laden schon
                        // entsprechend gesetzt.
                        
                        // Wurde das Widget manuell geloescht, soll nicht wieder automatisch der einzige Suchvorschlag
                        // ausgewaehlt werden.
                        suppressAutoSelectSingleSuggestion = true;
                    } else if ({$this->getId()}_jquery.data("_filterSetterUpdate")) {
                        // Update durch eine filter-Referenz.
                        
                        // Ergibt die Anfrage bei einem FilterSetterUpdate keine Ergebnisse waehrend ein Wert
                        // gesetzt ist, widerspricht der gesetzte Wert wahrscheinlich den gesetzten Filtern.
                        // Deshalb wird der Wert der ComboTable geloescht und anschliessend neu geladen.
                        var rows = {$this->getId()}_datagrid.datagrid("getData");
                        if (rows["total"] == 0 && {$this->buildJsFunctionPrefix()}valueGetter()) {
                            {$this->buildJsFunctionPrefix()}clear(true);
                            {$this->getId()}_datagrid.datagrid("reload");
                        }
                        
                        {$this->getId()}_jquery.removeData("_valueSetterUpdate");
                        {$this->getId()}_jquery.removeData("_clearFilterSetterUpdate");
                        {$this->getId()}_jquery.removeData("_filterSetterUpdate");
                        {$this->getId()}_jquery.removeData("_clear");
                        
                        // Wurde das Widget ueber eine Filter-Referenz befuellt (lazy-loading-group), werden
                        // keine Filter-Referenzen aktualisiert, denn alle Elemente der Gruppe werden vom
                        // Orginalobjekt bedient (wurde es hingegen manuell befuellt (autoselect) muessen
                        // die Filter-Referenzen bedient werden).
                        suppressLazyLoadingGroupUpdate = true;
                    } else if ({$this->getId()}_jquery.data("_clear")) {
                        // Loeschen des Wertes
                        
                        {$this->getId()}_jquery.removeData("_valueSetterUpdate");
                        {$this->getId()}_jquery.removeData("_clearFilterSetterUpdate");
                        {$this->getId()}_jquery.removeData("_filterSetterUpdate");
                        {$this->getId()}_jquery.removeData("_clear");
                        
                        // Wurde das Widget manuell geloescht, soll nicht wieder automatisch der einzige Suchvorschlag
                        // ausgewaehlt werden.
                        suppressAutoSelectSingleSuggestion = true;
                    }
                    
                    // Das resultSet wurde neu geladen und ist daher unveraendert. Ein erneutes Laden mit
                    // identischem filterSet kann unterbunden werden (siehe onBeforeLoad).
                    {$this->getId()}_jquery.data("_resultSetChanged", false);
JS;
        
        if ($widget->getAutoselectSingleSuggestion()) {
            $output .= <<<JS

                    if (!suppressAutoSelectSingleSuggestion) {
                        // Automatisches Auswaehlen des einzigen Suchvorschlags.
                        var rows = {$this->getId()}_datagrid.datagrid("getData");
                        if (rows["total"] == 1) {
                            var selectedRows = {$this->getId()}_datagrid.datagrid("getSelections");
                            if (selectedRows.length == 0 || selectedRows.length > 1 || selectedRows[0]["{$uidColumnName}"] != rows["rows"][0]["{$uidColumnName}"]) {
                                // Fuer multi_select werden erst alle angewaehlten Werte entfernt.
                                {$this->buildJsFunctionPrefix()}clear(true);
                                {$suppressLazyLoadingGroupUpdateScript}
                                // Beim Autoselect wurde ja zuvor schon geladen und es gibt nur noch einen Vorschlag
                                // im Resultat (im Gegensatz zur manuellen Auswahl eines Ergebnisses aus einer Liste).
                                {$this->getId()}_jquery.data("_suppressReloadOnSelect", true);
                                // onSelect wird getriggert
                                {$this->getId()}_datagrid.datagrid("selectRow", 0);
                                {$this->getId()}_jquery.combogrid("setText", rows["rows"][0]["{$textColumnName}"]);
                                {$this->getId()}_jquery.combogrid("hidePanel");
                            }
                        }
                    }
                    
JS;
        }
        
        return $output;
    }

    /**
     * Creates javascript-code which is executed after the erroneous loading of auto-
     * suggest-data.
     *
     * @return string
     */
    function buildJsOnLoadError()
    {
        $widget = $this->getWidget();
        
        $output = <<<JS

                    {$this->buildJsDebugMessage('onLoadError')}
                    var {$this->getId()}_jquery = $("#{$this->getId()}");
                    
                    {$this->getId()}_jquery.removeData("_valueSetterUpdate");
                    {$this->getId()}_jquery.removeData("_clearFilterSetterUpdate");
                    {$this->getId()}_jquery.removeData("_filterSetterUpdate");
                    {$this->getId()}_jquery.removeData("_clear");
JS;
        
        return $output;
    }

    /**
     * Creates a javascript-function which empties the object.
     * If the object had a value
     * before, onChange is triggered by clearing it. If suppressAllUpdates = true is
     * passed to the function, linked elements are not updated by clearing the object.
     * This behavior is usefull, if the object should really just be cleared.
     *
     * @return string
     */
    function buildJsClearFunction()
    {
        $widget = $this->getWidget();
        
        $output = <<<JS

                function {$this->buildJsFunctionPrefix()}clear(suppressAllUpdates) {
                    {$this->buildJsDebugMessage('clear()')}
                    var {$this->getId()}_jquery = $("#{$this->getId()}");
                    
                    // Bestimmt ob durch das Leeren andere verlinkte Elemente aktualisiert werden sollen. 
                    {$this->getId()}_jquery.data("_otherSuppressAllUpdates", suppressAllUpdates);
                    // Beim Leeren wird die LazyLoadingGroup (wenn es eine gibt) nicht aktualisiert.
                    {$this->getId()}_jquery.data("_otherSuppressLazyLoadingGroupUpdate", true);
                    // Durch das Leeren aendert sich das resultSet und es sollte das naechste Mal neu geladen
                    // werden, auch wenn sich das Filterset nicht geaendert hat (siehe onBeforeLoad).
                    {$this->getId()}_jquery.data("_resultSetChanged", true);
                    // Triggert onChange, wenn vorher ein Element ausgewaehlt war.
                    {$this->getId()}_jquery.combogrid("clear");
                    // Wurde das Widget bereits manuell geleert, wird mit clear kein onChange getriggert und
                    // _otherSuppressAllUpdates nicht entfernt. Wird clear mit _otherSuppressAllUpdates
                    // gestartet, dann ist hinterher _clearFilterSetterUpdate gesetzt. Daher werden hier
                    // vorsichtshalber _otherSuppressAllUpdates und _clearFilterSetterUpdate manuell geloescht.
                    {$this->getId()}_jquery.removeData("_otherSuppressAllUpdates");
                    {$this->getId()}_jquery.removeData("_otherSuppressLazyLoadingGroupUpdate");
                    {$this->getId()}_jquery.removeData("_clearFilterSetterUpdate");
                }
JS;
        return $output;
    }

    function getJsDebugLevel()
    {
        return $this->js_debug_level;
    }

    /**
     * Determines the detail-level of the debug-messages which are written to the browser-
     * console.
     * 0 = off, 1 = low, 2 = medium, 3 = high detail-level (default: 0)
     *
     * @param integer|string $value            
     * @return \exface\JEasyUiTemplate\Template\Elements\euiComboTable
     */
    function setJsDebugLevel($value)
    {
        if (is_int($value)) {
            $this->js_debug_level = $value;
        } else if (is_string($value)) {
            $this->js_debug_level = intval($value);
        } else {
            throw new InvalidArgumentException('Can not set js_debug_level for "' . $this->getId() . '": the argument passed to set_js_debug_level() is neither an integer nor a string!');
        }
        return $this;
    }

    /**
     * Creates javascript-code that writes a debug-message to the browser-console.
     *
     * @param string $source            
     * @return string
     */
    function buildJsDebugMessage($source)
    {
        switch ($this->getJsDebugLevel()) {
            case 0:
                $output = '';
                break;
            case 1:
            case 2:
                $output = <<<JS
                if (window.console) { console.debug(Date.now() + "|{$this->getId()}.{$source}"); }
JS;
                break;
            case 3:
                $output = <<<JS
                if (window.console) { console.debug(Date.now() + "|{$this->getId()}.{$source}|" + {$this->buildJsFunctionPrefix()}debugDataToString()); }
JS;
                break;
            default:
                $output = '';
        }
        return $output;
    }

    /**
     * Creates a javascript-function, which returns a string representation of the content
     * of private variables which are stored in the data-object of the element and which
     * are important for the function of the object.
     * It is required for debug-messages with
     * a high detail-level.
     *
     * @return string
     */
    function buildJsDebugDataToStringFunction()
    {
        $output = <<<JS
		
                function {$this->buildJsFunctionPrefix()}debugDataToString() {
                    var {$this->getId()}_jquery = $("#{$this->getId()}");
                    
                    var output =
                        "_valueSetterUpdate: " + {$this->getId()}_jquery.data("_valueSetterUpdate") + ", " +
                        "_filterSetterUpdate: " + {$this->getId()}_jquery.data("_filterSetterUpdate") + ", " +
                        "_clearFilterSetterUpdate: " + {$this->getId()}_jquery.data("_clearFilterSetterUpdate") + ", " +
                        "_firstLoad: " + {$this->getId()}_jquery.data("_firstLoad") + ", " +
                        "_otherSuppressFilterSetterUpdate: " + {$this->getId()}_jquery.data("_otherSuppressFilterSetterUpdate") + ", " +
                        "_otherClearFilterSetterUpdate: " + {$this->getId()}_jquery.data("_otherClearFilterSetterUpdate") + ", " +
                        "_otherSuppressAllUpdates: " + {$this->getId()}_jquery.data("_otherSuppressAllUpdates") + ", " +
                        "_otherSuppressLazyLoadingGroupUpdate: " + {$this->getId()}_jquery.data("_otherSuppressLazyLoadingGroupUpdate") + ", " +
                        "_suppressReloadOnSelect: " + {$this->getId()}_jquery.data("_suppressReloadOnSelect") + ", " +
                        "_currentText: " + {$this->getId()}_jquery.data("_currentText") + ", " +
                        "_currentSelections: " + {$this->getId()}_jquery.data("_currentSelections") + ", " +
                        "_lastFilterSet: "+ JSON.stringify({$this->getId()}_jquery.data("_lastFilterSet")) + ", " +
                        "_resultSetChanged: " + {$this->getId()}_jquery.data("_resultSetChanged");
                    return output;
                }
JS;
        return $output;
    }

    /**
     *
     * @return string
     */
    function buildJsInitFunction()
    {
        $widget = $this->getWidget();
        
        $uidColumnName = $widget->getTable()->getUidColumn()->getDataColumnName();
        $multiSelect = $widget->getMultiSelect() ? 'true' : 'false';
        $lazyLoadingGroup = $widget->getLazyLoadingGroupId() ? 'true' : 'false';
        
        $output = <<<JS

                function {$this->buildJsFunctionPrefix()}init() {
                    var {$this->getId()}_jquery = $("#{$this->getId()}");
                    
                    {$this->getId()}_jquery.data("_currentSelections", []);
                    {$this->getId()}_jquery.data("_uidColumnName", "$uidColumnName");
                    {$this->getId()}_jquery.data("_multiSelect", $multiSelect);
                    {$this->getId()}_jquery.data("_inLazyLoadingGroup", $lazyLoadingGroup);
                }
JS;
        
        return $output;
    }
}
?>