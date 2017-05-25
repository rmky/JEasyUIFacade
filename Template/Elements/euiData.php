<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\DataColumnGroup;
use exface\Core\Widgets\Data;
use exface\Core\CommonLogic\DataSheets\DataSheet;

/**
 * Implementation of a basic grid.
 *
 * @method Data get_widget()
 *        
 * @author Andrej Kabachnik
 *        
 */
class euiData extends euiAbstractElement
{

    private $toolbar_id = null;

    private $show_footer = null;

    private $editable = false;

    private $editors = array();

    private $on_before_load = '';

    private $on_load_success = '';

    private $on_load_error = '';

    private $load_filter_script = '';

    private $headers_colspan = array();

    private $headers_rowspan = array();

    public function generateHtml()
    {
        return '';
    }

    public function generateJs()
    {
        return '';
    }

    protected function init()
    {
        /* @var $col \exface\Core\Widgets\DataColumn */
        foreach ($this->getWidget()->getColumns() as $col) {
            // handle editors
            if ($col->isEditable()) {
                $editor = $this->getTemplate()->getElement($col->getEditor(), $this->getPageId());
                $this->setEditable(true);
                $this->editors[$col->getId()] = $editor;
            }
        }
    }

    /**
     * Generates config-elements for the js grid instatiator, that define the data source for the grid.
     * By default the data source is remote and will be fetched via AJAX. Override this method for local data sources.
     *
     * @return string
     */
    public function buildJsDataSource()
    {
        $widget = $this->getWidget();
        
        if ($widget->getLazyLoading()) {
            // Lazy loading via AJAX
            $params = array();
            $queryParams = array(
                'resource' => $this->getPageId(),
                'element' => $widget->getId(),
                'object' => $this->getWidget()->getMetaObject()->getId(),
                'action' => $widget->getLazyLoadingAction()
            );
            foreach ($queryParams as $param => $val) {
                $params[] = $param . ': "' . $val . '"';
            }
            
            // Add initial filters
            if ($this->getWidget()->hasFilters()) {
                foreach ($this->getWidget()->getFilters() as $fnr => $fltr) {
                    // If the filter is a live reference, add the code to use it to the onBeforeLoad event
                    if ($link = $fltr->getValueWidgetLink()) {
                        $linked_element = $this->getTemplate()->getElementByWidgetId($link->getWidgetId(), $this->getPageId());
                        $live_filter_js .= 'param.fltr' . str_pad($fnr, 2, 0, STR_PAD_LEFT) . '_' . urlencode($fltr->getAttributeAlias()) . '= "' . $fltr->getComparator() . '"+' . $linked_element->buildJsValueGetter() . ';';
                        $this->addOnBeforeLoad($live_filter_js);
                    } // If the filter has a static value, just set it here
else {
                        $params[] = 'fltr' . str_pad($fnr, 2, 0, STR_PAD_LEFT) . '_' . urlencode($fltr->getAttributeAlias()) . ': "' . $fltr->getComparator() . urlencode(strpos($fltr->getValue(), '=') === 0 ? '' : $fltr->getValue()) . '"';
                    }
                }
            }
            $result = '
				url: "' . $this->getAjaxUrl() . '"
				, queryParams: {' . implode("\n\t\t\t\t\t, ", $params) . '}';
        } else {
            // Data embedded in the code of the DataGrid
            $data = $widget->prepareDataSheetToRead($widget->getValuesDataSheet());
            if (! $data->isFresh()) {
                $data->dataRead();
            }
            $result = '
				remoteSort: false
				, loader: function(param, success, error) {' . $this->buildJsDataLoaderWithoutAjax($data) . '}';
        }
        
        return $result;
    }

    public function buildJsInitOptionsHead()
    {
        /* @var $widget \exface\Core\Widgets\Data */
        $widget = $this->getWidget();
        
        // add initial sorters
        $sort_by = array();
        $direction = array();
        if ($widget->getLazyLoading() && count($widget->getSorters()) > 0) {
            foreach ($widget->getSorters() as $sort) {
                $sort_by[] = urlencode($sort->attribute_alias);
                $direction[] = urlencode($sort->direction);
            }
            $sortColumn = ", sortName: '" . implode(',', $sort_by) . "'";
            $sortOrder = ", sortOrder: '" . implode(',', $direction) . "'";
        }
        
        // Make sure, all selections are cleared, when the data is loaded from the backend. This ensures, the selected rows are always visible to the user!
        if ($widget->getMultiSelect()) {
            $this->addOnLoadSuccess('$(this).' . $this->getElementType() . '("clearSelections");');
        }
        
        $output = '
				, rownumbers: ' . ($widget->getShowRowNumbers() ? 'true' : 'false') . '
				, fitColumns: true
				, multiSort: ' . ($widget->getHeaderSortMultiple() ? 'true' : 'false') . '
				' . $sortColumn . $sortOrder . '
				, showFooter: "' . ($this->getShowFooter() ? 'true' : 'false') . '"
				' . ($widget->getUidColumnId() ? ', idField: "' . $widget->getUidColumn()->getDataColumnName() . '"' : '') . '
				' . (! $widget->getMultiSelect() ? ', singleSelect: true' : '') . '
				' . ($this->getWidth() ? ', width: "' . $this->getWidth() . '"' : '') . '
				, pagination: ' . ($widget->getPaginate() ? 'true' : 'false') . '
				, pageList: ' . json_encode($this->getTemplate()->getApp()->getConfig()->getOption('WIDGET.DATATABLE.PAGE_SIZES_SELECTABLE')) . '
				, pageSize: ' . $widget->getPaginateDefaultPageSize() . '
				, striped: ' . ($widget->getStriped() ? 'true' : 'false') . '
				, nowrap: ' . ($widget->getNowrap() ? 'true' : 'false') . '
				, toolbar: "#' . $this->getToolbarId() . '"
				' . ($this->getOnBeforeLoad() ? ', onBeforeLoad: function(param) {
					' . $this->getOnBeforeLoad() . '
				}' : '') . '
				' . ($this->getOnLoadSuccess() ? ', onLoadSuccess: function(data) {
					' . $this->getOnLoadSuccess() . '
				}' : '') . '
				, onLoadError: function(response) {
					' . $this->buildJsShowError('response.responseText', 'response.status + " " + response.statusText') . '
					' . $this->getOnLoadError() . '
				}
				' . ($this->getLoadFilterScript() ? ', loadFilter: function(data) {
					' . $this->getLoadFilterScript() . '
					return data;
				}' : '') . '
				, columns: [ ' . implode(',', $this->buildJsInitOptionsColumns()) . ' ]';
        return $output;
    }

    public function buildJsInitOptionsColumns(array $column_groups = null)
    {
        if (! $column_groups) {
            $column_groups = $this->getWidget()->getColumnGroups();
        }
        
        // render the columns
        $header_rows = array();
        $full_height_column_groups = array();
        if ($this->getWidget()->getMultiSelect()) {
            $header_rows[0][0] = '{field: "ck", checkbox: true}';
        }
        /* @var $column_group \exface\Core\Widgets\DataColumnGroup */
        // Set the rowspan for column groups with a caption and remember those without a caption to set the colspan later
        foreach ($column_groups as $column_group) {
            if (! $column_group->getCaption()) {
                $full_height_column_groups[] = $column_group;
            }
        }
        // Now set colspan = 2 for all full height columns, if there are two rows of columns
        if (count($full_height_column_groups) != count($column_groups)) {
            foreach ($full_height_column_groups as $column_group) {
                $this->setColumnHeaderRowspan($column_group, 2);
            }
            if ($this->getWidget()->getMultiSelect()) {
                $header_rows[0][0] = '{field: "ck", checkbox: true, rowspan: 2}';
            }
        }
        // Now loop through all column groups again and built the header definition
        foreach ($column_groups as $column_group) {
            if ($column_group->getCaption()) {
                $header_rows[0][] = '{title: "' . str_replace('"', '\"', $column_group->getCaption()) . '", colspan: ' . $column_group->countColumnsVisible() . '}';
                $put_into_header_row = 1;
            } else {
                $put_into_header_row = 0;
            }
            foreach ($column_group->getColumns() as $col) {
                $header_rows[$put_into_header_row][] = $this->buildJsInitOptionsColumn($col);
                if ($col->hasFooter())
                    $this->setShowFooter(true);
            }
        }
        
        foreach ($header_rows as $i => $row) {
            $header_rows[$i] = '[' . implode(',', $row) . ']';
        }
        
        return $header_rows;
    }

    protected function setColumnHeaderColspan(DataColumnGroup $column_group, $colspan)
    {
        foreach ($column_group->getColumns() as $col) {
            $this->headers_colspan[$col->getId()] = $colspan;
        }
        return $this;
    }

    protected function getColumnHeaderColspan($column_id)
    {
        return $this->headers_colspan[$column_id];
    }

    protected function setColumnHeaderRowspan(DataColumnGroup $column_group, $rowspan)
    {
        foreach ($column_group->getColumns() as $col) {
            $this->headers_rowspan[$col->getId()] = $rowspan;
        }
        return $this;
    }

    protected function getColumnHeaderRowspan($column_id)
    {
        return $this->headers_rowspan[$column_id];
    }

    public function buildJsInitOptionsColumn(\exface\Core\Widgets\DataColumn $col)
    {
        // set defaults
        $editor = $this->editors[$col->getId()];
        // TODO Settig "field" to the id of the column is dirty, since the data sheet column has
        // the attribute name for id. I don't know, why this actually works, because the field in the
        // JSON is named by attribute id, not column id. However, when getting the data from the table
        // via java script, the fields are named by the column id (as configured here).
        
        // FIXME Make compatible with column groups
        $colspan = $this->getColumnHeaderColspan($col->getId());
        $rowspan = $this->getColumnHeaderRowspan($col->getId());
        $output = '{
							title: "<span title=\"' . $this->buildHintText($col->getHint(), true) . '\">' . $col->getCaption() . '</span>"' . ($col->getAttributeAlias() ? ', field: "' . $col->getDataColumnName() . '"' : '') . ($colspan ? ', colspan: ' . intval($colspan) : '') . ($rowspan ? ', rowspan: ' . intval($rowspan) : '') . ($col->isHidden() ? ', hidden: true' : '') . ($col->getWidth()->isTemplateSpecific() ? ', width: "' . $col->getWidth()->toString() . '"' : '') . ($editor ? ', editor: {type: "' . $editor->getElementType() . '"' . ($editor->buildJsInitOptions() ? ', options: {' . $editor->buildJsInitOptions() . '}' : '') . '}' : '') . ($col->getCellStylerScript() ? ', styler: function(value,row,index){' . $col->getCellStylerScript() . '}' : '') . ', align: "' . $col->getAlign() . '"' . ', sortable: ' . ($col->getSortable() ? 'true' : 'false') . '}';
        
        return $output;
    }

    public function getToolbarId()
    {
        if (is_null($this->toolbar_id)) {
            $this->toolbar_id = $this->getId() . '_toolbar';
        }
        return $this->toolbar_id;
    }

    public function setToolbarId($value)
    {
        $this->toolbar_id = $value;
    }

    public function getShowFooter()
    {
        if (is_null($this->show_footer)) {
            $this->show_footer = ($this->getTemplate()->getConfig()->getOption('DATAGRID_SHOW_FOOTER_BY_DEFAULT') ? true : false);
        }
        return $this->show_footer;
    }

    public function setShowFooter($value)
    {
        $this->show_footer = $value;
    }

    public function isEditable()
    {
        return $this->editable;
    }

    public function setEditable($value)
    {
        $this->editable = $value;
    }

    public function getEditors()
    {
        return $this->editors;
    }

    /**
     * Add JS code to be executed on the OnBeforeLoad event of jEasyUI datagrid.
     * The script will have access to the "param" variable
     * representing all XHR parameters to be sent to the server.
     *
     * @param string $script            
     */
    public function addOnBeforeLoad($script)
    {
        $this->on_before_load .= $script;
    }

    /**
     * Set JS code to be executed on the OnBeforeLoad event of jEasyUI datagrid.
     * The script will have access to the "param" variable
     * representing all XHR parameters to be sent to the server.
     *
     * @param string $script            
     */
    public function setOnBeforeLoad($script)
    {
        $this->on_before_load = $script;
    }

    protected function getOnBeforeLoad()
    {
        $script = <<<JS
				if ($(this).{$this->getElementType()}('options')._skipNextLoad == true) {
					$(this).{$this->getElementType()}('options')._skipNextLoad = false;
					return false;
				}
				{$this->on_before_load}
JS;
        return $script;
    }

    /**
     * Binds a script to the onLoadSuccess event.
     *
     * @param string $script            
     */
    public function addOnLoadSuccess($script)
    {
        $this->on_load_success .= $script;
    }

    protected function getOnLoadSuccess()
    {
        return $this->on_load_success;
    }

    /**
     * Binds a script to the onLoadError event.
     *
     * @param string $script            
     */
    public function addOnLoadError($script)
    {
        $this->on_load_error .= $script;
    }

    protected function getOnLoadError()
    {
        return $this->on_load_error;
    }

    public function addOnChangeScript($string)
    {
        return $this->addOnLoadSuccess($string);
    }

    public function addLoadFilterScript($javascript)
    {
        $this->load_filter_script .= $javascript;
    }

    public function getLoadFilterScript()
    {
        return $this->load_filter_script;
    }

    public function buildJsDataLoaderWithoutAjax(DataSheet $data)
    {
        $js = <<<JS
		
		try {
			var data = {$this->getTemplate()->encodeData($this->prepareData($data))};
		} catch (err){
			error();
			return;
		}
		
		var filter, value, total = data.rows.length;
		for(var p in param){
			if (p.startsWith("fltr")){
				column = p.substring(7);	
				value = param[p];
			}
			
			if (value){
				var regexp = new RegExp(value, 'i');
				for (var row=0; row<total; row++){
					if (data.rows[row] && typeof data.rows[row][column] !== 'undefined'){
						if (!data.rows[row][column].match(regexp)){
							data.rows.splice(row, 1);
						}
					}
				}
			}
		}
		data.total = data.rows.length;
		success(data);	
		return;
JS;
        return $js;
    }

    public function buildJsInitOptions()
    {
        return $this->buildJsDataSource() . $this->buildJsInitOptionsHead();
    }
}
?>