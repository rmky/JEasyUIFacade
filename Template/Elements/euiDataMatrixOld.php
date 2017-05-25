<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\CommonLogic\DataSheets\DataColumn;

class euiDataMatrixOld extends euiDataTable
{

    private $label_values = array();

    protected function init()
    {
        parent::init();
        $this->setElementType('datagrid');
    }

    function generateJs()
    {
        $widget = $this->getWidget();
        $output = '';
        
        if ($this->isEditable()) {
            foreach ($this->getEditors() as $editor) {
                $output .= $editor->buildJsInlineEditorInit();
            }
        }
        
        // get the standard params for grids
        $grid_head = $this->buildJsInitOptions();
        
        // instantiate the data grid
        $output .= '$("#' . $this->getId() . '").' . $this->getElementType() . '({' . $grid_head . '});';
        
        // doSearch function for the filters
        if ($widget->hasFilters()) {
            foreach ($widget->getFilters() as $fnr => $fltr) {
                $fltr_impl = $this->getTemplate()->getElement($fltr, $this->getPageId());
                $output .= $fltr_impl->generateJs();
                $fltrs[] = '"fltr' . str_pad($fnr, 2, 0, STR_PAD_LEFT) . '_' . urlencode($fltr->getAttributeAlias()) . '": ' . $fltr_impl->buildJsValueGetter();
            }
            // build JS for the search function
            $output .= '
						function ' . $this->buildJsFunctionPrefix() . 'doSearch(){
							$("#' . $this->getId() . '").' . $this->getElementType() . '("load",{' . implode(', ', $fltrs) . ', resource: "' . $this->getPageId() . '", element: "' . $this->getWidget()->getId() . '"});
						}';
        }
        
        return $output;
    }

    /**
     * This special data source renderer fetches data according to the filters an reorganizes the rows and column to fit the matrix.
     * It basically transposes the data column (data_column_id) using values of the label column (label_column_id) as new column headers.
     * The other columns remain untouched.
     * 
     * @see \exface\JEasyUiTemplate\Template\Elements\grid::buildJsDataSource()
     */
    public function buildJsDataSource()
    {
        /* @var $widget \exface\Core\Widgets\DataMatrix  */
        $widget = $this->getWidget();
        $visible_columns = array();
        $output = '';
        $result = array();
        
        // create data sheet to fetch data
        $ds = $this->getTemplate()
            ->getWorkbench()
            ->data()
            ->createDataSheet($this->getMetaObject());
        // add columns
        foreach ($widget->getColumns() as $col) {
            $ds->getColumns()->addFromExpression($col->getAttributeAlias(), $col->getDataColumnName(), $col->isHidden());
            if (! $col->isHidden())
                $visible_columns[] = $col->getDataColumnName();
        }
        // add the filters
        foreach ($widget->getFilters() as $fw) {
            if (! is_null($fw->getValue())) {
                $ds->addFilterFromString($fw->getAttributeAlias(), $fw->getValue());
            }
        }
        // add the sorters
        foreach ($widget->getSorters() as $sort) {
            $ds->getSorters()->addFromString($sort->attribute_alias, $sort->direction);
        }
        // add aggregators
        foreach ($widget->getAggregations() as $aggr) {
            $ds->getAggregators()->addFromString($aggr);
        }
        
        // get the data
        $ds->dataRead();
        $label_col = $widget->getLabelColumn();
        $data_col = $widget->getDataColumn();
        foreach ($ds->getRows() as $nr => $row) {
            $new_row_id = null;
            $new_row = array();
            $new_col_val = null;
            $new_col_id = null;
            foreach ($row as $fld => $val) {
                
                if ($fld === $label_col->getDataColumnName()) {
                    $new_col_id = $val;
                    // TODO we probably need a special parameter for sorting labels!
                    if (! in_array($val, $this->label_values))
                        $this->label_values[] = $val;
                } elseif ($fld === $data_col->getDataColumnName()) {
                    $new_col_val = $val;
                } elseif (in_array($fld, $visible_columns)) {
                    $new_row_id .= $val;
                    $new_row[$fld] = $val;
                }
            }
            if (! is_array($result[$new_row_id])) {
                $result[$new_row_id] = $new_row;
            }
            $result[$new_row_id][$new_col_id] = $new_col_val;
        }
        
        $output = "data: [";
        foreach ($result as $row) {
            $output .= "{";
            foreach ($row as $fld => $val) {
                $output .= '"' . $this->cleanId($fld) . '": "' . str_replace('"', '\"', $val) . '",';
            }
            $output = substr($output, 0, - 1);
            $output .= '},';
        }
        $output = substr($output, 0, - 1);
        $output .= ']';
        return $output;
    }

    /**
     * This special column renderer for the matrix replaces the column specified by label_column_id with a set of new columns for
     * every unique value in the column specified by data_column_id.
     * The new columns retain most properties of the replaced label column.
     * 
     * @see \exface\JEasyUiTemplate\Template\Elements\grid::buildJsInitOptionsColumns()
     */
    public function buildJsInitOptionsColumns(array $column_groups = null)
    {
        $widget = $this->getWidget();
        $cols = $this->getWidget()->getColumns();
        $new_cols = $widget->getPage()->createWidget('DataColumnGroup', $widget);
        foreach ($cols as $id => $col) {
            if ($col->getId() === $this->getWidget()->getDataColumnId()) {
                // replace the data column with a new set of columns for each possible label
                foreach ($this->label_values as $label) {
                    $new_col = clone ($col);
                    $new_col->setDataColumnName(DataColumn::sanitizeColumnName($label));
                    $new_col->setCaption($label);
                    $new_col->setSortable(false);
                    $new_cols->addColumn($new_col);
                }
            } elseif ($col->getId() === $this->getWidget()->getLabelColumnId()) {
                // doing nothing here makes the label column disapear
            } else {
                $new_cols->addColumn($col);
            }
        }
        
        return parent::buildJsInitOptionsColumns(array(
            $new_cols
        ));
    }
}
?>