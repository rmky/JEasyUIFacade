<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement;
use exface\JEasyUiTemplate\Template\JEasyUiTemplate;
use exface\Core\Interfaces\Widgets\iLayoutWidgets;
use exface\Core\Interfaces\Widgets\iFillEntireContainer;
use exface\Core\Widgets\Panel;
use exface\Core\Widgets\Dialog;
use exface\Core\Widgets\DataTable;
use exface\Core\Widgets\Chart;

abstract class euiAbstractElement extends AbstractJqueryElement
{
    
    // px
    private $spacing = 8;
    // px
    private $borderWidth = 1;
    // relative units
    private $largeWidgetDefaultHeight = 10;

    private $number_of_columns = null;

    private $searchedForNumberOfColumns = false;

    public function buildJsInitOptions()
    {
        return '';
    }

    public function buildJsInlineEditorInit()
    {
        return '';
    }

    /**
     *
     * @return JEasyUiTemplate
     */
    public function getTemplate()
    {
        return parent::getTemplate();
    }

    public function escapeString($string)
    {
        return str_replace('"', "'", $string);
    }

    public function prepareData(\exface\Core\Interfaces\DataSheets\DataSheetInterface $data_sheet)
    {
        // apply the formatters
        foreach ($data_sheet->getColumns() as $name => $col) {
            if ($formatter = $col->getFormatter()) {
                $expr = $formatter->toString();
                $function = substr($expr, 1, strpos($expr, '(') - 1);
                $formatter_class_name = 'formatters\'' . $function;
                if (class_exists($class_name)) {
                    $formatter = new $class_name($y);
                }
                
                // See if the formatter returned more results, than there were rows. If so, it was also performed on
                // the total rows. In this case, we need to slice them off and pass to set_column_values() separately.
                // This only works, because evaluating an expression cannot change the number of data rows! This justifies
                // the assumption, that any values after count_rows() must be total values.
                $vals = $formatter->evaluate($data_sheet, $name);
                if ($data_sheet->countRows() < count($vals)) {
                    $totals = array_slice($vals, $data_sheet->countRows());
                    $vals = array_slice($vals, 0, $data_sheet->countRows());
                }
                $data_sheet->setColumnValues($name, $vals, $totals);
            }
        }
        $data = array();
        $data['rows'] = $data_sheet->getRows();
        $data['total'] = $data_sheet->countRowsAll();
        $data['footer'] = $data_sheet->getTotalsRows();
        return $data;
    }

    public function buildJsBusyIconShow()
    {
        return "$.messager.progress({});";
    }

    public function buildJsBusyIconHide()
    {
        return "$.messager.progress('close');";
    }

    public function buildJsShowError($message_body_js, $title_js = null)
    {
        $title_js = ! is_null($title_js) ? $title_js : '"Error"';
        return 'jeasyui_create_dialog($("body"), "' . $this->getId() . '_error", {title: ' . $title_js . ', width: 800, height: "80%"}, ' . $message_body_js . ', true);';
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::buildJsShowMessageSuccess($message_body_js, $title)
     */
    public function buildJsShowMessageError($message_body_js, $title = null)
    {
        $title = ! is_null($title) ? $title : '"' . $this->translate('MESSAGE.ERROR_TITLE') . '"';
        return "$.messager.alert(" . $title . "," . $message_body_js . ",'error');";
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::buildJsShowMessageSuccess($message_body_js, $title)
     */
    public function buildJsShowMessageSuccess($message_body_js, $title = null)
    {
        $title = ! is_null($title) ? $title : "'" . $this->translate('MESSAGE.SUCCESS_TITLE') . "'";
        return "$.messager.show({
					title: " . str_replace('"', '\"', $title) . ",
	                msg: " . $message_body_js . ",
	                timeout:5000,
	                showType:'slide'
	            });";
    }

    public function getMasonryItemClass()
    {
        $output = '';
        if ($layoutWidget = $this->getWidget()->getLayoutWidget()) {
            $output = $this->getTemplate()->getElement($layoutWidget)->getId() . '_masonry_fitem';
        }
        return $output;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::getWidth()
     */
    public function getWidth()
    {
        $widget = $this->getWidget();
        
        if ($layoutWidget = $widget->getLayoutWidget()) {
            $columnNumber = $this->getTemplate()->getElement($layoutWidget)->getNumberOfColumns();
        } else {
            $columnNumber = $this->getTemplate()->getConfig()->getOption("COLUMNS_BY_DEFAULT");
        }
        
        $dimension = $widget->getWidth();
        if ($dimension->isRelative()) {
            $cols = $dimension->getValue();
            if ($cols === 'max') {
                $cols = $columnNumber;
            }
            if (is_numeric($cols)) {
                if ($cols < 1) {
                    $cols = 1;
                } else if ($cols > $columnNumber) {
                    $cols = $columnNumber;
                }
                
                if ($cols == $columnNumber) {
                    $output = '100%';
                } else {
                    $output = 'calc(100% * ' . $cols . '/' . $columnNumber . ')';
                }
            } else {
                $output = 'calc(100% / ' . $columnNumber . ')';
            }
        } elseif ($dimension->isTemplateSpecific() || $dimension->isPercentual()) {
            $output = $dimension->getValue();
        } elseif ($widget instanceof iFillEntireContainer) {
            // Ein "grosses" Widget ohne angegebene Breite.
            $output = '100%';
        } else {
            // Ein "kleines" Widget ohne angegebene Breite.
            $output = 'calc(100% / ' . $columnNumber . ')';
        }
        return $output;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::getHeight()
     */
    public function getHeight()
    {
        $widget = $this->getWidget();
        $layoutWidget = $widget->getLayoutWidget();
        
        $dimension = $widget->getHeight();
        if ($dimension->isRelative()) {
            $output = $this->getHeightRelativeUnit() * $dimension->getValue() . 'px';
        } elseif ($dimension->isTemplateSpecific() || $dimension->isPercentual()) {
            $output = $dimension->getValue();
        } elseif ($widget instanceof iFillEntireContainer) {
            // Ein "grosses" Widget ohne angegebene Hoehe.
            $output = '100%';
            if ($layoutWidget && ($layoutWidget->countWidgets() > 1)) {
                //$output = 'auto';
                $output = ($this->getHeightRelativeUnit() * $this->getLargeWidgetDefaultHeight()) . 'px';
            }
        } else {
            // Ein "kleines" Widget ohne angegebene Hoehe.
            $output = ($this->getHeightRelativeUnit() * $this->getHeightDefault()) . 'px';
        }
        return $output;
    }

    public function getMinWidth()
    {
        if ($this->getWidget() instanceof iLayoutWidgets) {
            // z.B. die Filter-Widgets der DataTables sind genau getWidthRelativeUnits breit und
            // wuerden sonst vom Rand teilweise verdeckt werden.
            $output = ($this->getWidthMinimum() + $this->getSpacing() + 2 * $this->getBorderWidth()) . 'px';
        } else {
            $output = $this->getWidthMinimum() . 'px';
        }
        return $output;
    }

    public function getNumberOfColumns()
    {
        if (! $this->searchedForNumberOfColumns) {
            $widget = $this->getWidget();
            if ($widget instanceof iLayoutWidgets) {
                if (! is_null($widget->getNumberOfColumns())) {
                    $this->number_of_columns = $widget->getNumberOfColumns();
                } else {
                    if ($layoutWidget = $widget->getLayoutWidget()) {
                        $parentColumnNumber = $this->getTemplate()->getElement($layoutWidget)->getNumberOfColumns();
                    }
                    switch (true) {
                        case $widget instanceof Dialog:
                            $defaultColumnNumber = $this->getTemplate()->getConfig()->getOption("WIDGET.DIALOG.COLUMNS_BY_DEFAULT");
                            break;
                        case $widget instanceof Panel:
                            $defaultColumnNumber = $this->getTemplate()->getConfig()->getOption("WIDGET.PANEL.COLUMNS_BY_DEFAULT");
                            break;
                        case $widget instanceof DataTable:
                            $defaultColumnNumber = $this->getTemplate()->getConfig()->getOption("WIDGET.DATATABLE.COLUMNS_BY_DEFAULT");
                            break;
                        case $widget instanceof Chart:
                            $defaultColumnNumber = $this->getTemplate()->getConfig()->getOption("WIDGET.CHART.COLUMNS_BY_DEFAULT");
                    }
                    if (is_null($parentColumnNumber) || $defaultColumnNumber < $parentColumnNumber) {
                        $this->number_of_columns = $defaultColumnNumber;
                    } else {
                        $this->number_of_columns = $parentColumnNumber;
                    }
                }
            }
            $this->searchedForNumberOfColumns = true;
        }
        return $this->number_of_columns;
    }

    public function getSpacing()
    {
        return $this->spacing;
    }

    public function getPadding()
    {
        $output = '0';
        if (($layoutWidget = $this->getWidget()->getLayoutWidget()) && ($layoutWidget->countWidgets() > 1)) {
            $output = round($this->getSpacing() / 2) . 'px';
        }
        return $output;
    }

    public function getBorderWidth()
    {
        return $this->borderWidth;
    }

    public function getLargeWidgetDefaultHeight()
    {
        return $this->largeWidgetDefaultHeight;
    }
}
?>