<?php
namespace exface\JEasyUiTemplate\Template\Elements;
use exface\Core\CommonLogic\DataSheets\DataColumn;

class euiDataMatrix extends euiDataTable {
	private $label_values = array();
	
	protected function init(){
		parent::init();
		$this->set_element_type('datagrid');
	}
	
	function generate_js(){
		$widget = $this->get_widget();
		$output = '';
		
		if ($this->is_editable()){
			foreach ($this->get_editors() as $editor){
				$output .= $editor->build_js_inline_editor_init();
			}
		}
		
		// get the standard params for grids
		$grid_head = $this->render_grid_head();
		
		// instantiate the data grid
		$output .= '$("#' . $this->get_id() . '").' . $this->get_element_type() . '({' . $grid_head . '});';
		
		// doSearch function for the filters
		if ($widget->has_filters()){
			foreach($widget->get_filters() as $fnr => $fltr){
				$fltr_impl = $this->get_template()->get_element($fltr, $this->get_page_id());
				$output .= $fltr_impl->generate_js();
				$fltrs[] = '"fltr' . str_pad($fnr, 2, 0, STR_PAD_LEFT) . '_' . urlencode($fltr->get_attribute_alias()) . '": ' . $fltr_impl->build_js_value_getter();
			}
			// build JS for the search function
			$output .= '
						function ' . $this->build_js_function_prefix() . 'doSearch(){
							$("#' . $this->get_id() . '").' . $this->get_element_type() . '("load",{' . implode(', ', $fltrs) . ', resource: "' . $this->get_page_id() . '", element: "' .  $this->get_widget()->get_id() . '"});
						}';
		}
		
		return $output;
	}
	
	/**
	 * This special data source renderer fetches data according to the filters an reorganizes the rows and column to fit the matrix.
	 * It basically transposes the data column (data_column_id) using values of the label column (label_column_id) as new column headers.
	 * The other columns remain untouched.
	 * @see \exface\JEasyUiTemplate\Template\Elements\grid::render_data_source()
	 */
	public function render_data_source(){
		/* @var $widget \exface\Core\Widgets\DataMatrix  */
		$widget = $this->get_widget();
		$visible_columns = array();
		$output = '';
		$result = array();
				
		// create data sheet to fetch data
		$ds = $this->get_template()->get_workbench()->data()->create_data_sheet($this->get_meta_object());
		// add columns
		foreach ($widget->get_columns() as $col){
			$ds->get_columns()->add_from_expression($col->get_attribute_alias(), $col->get_data_column_name(), $col->is_hidden());
			if (!$col->is_hidden()) $visible_columns[] = $col->get_data_column_name();
		}
		// add the filters
		foreach ($widget->get_filters() as $fw){
			if (!is_null($fw->get_value())){
				$ds->add_filter_from_string($fw->get_attribute_alias(), $fw->get_value());
			}
		}
		// add the sorters
		foreach ($widget->get_sorters() as $sort){
			$ds->get_sorters()->add_from_string($sort->attribute_alias, $sort->direction);
		}
		// add aggregators
		foreach ($widget->get_aggregations() as $aggr){
			$ds->get_aggregators()->add_from_string($aggr);
		}
		
		// get the data
		$ds->data_read();
		$label_col = $widget->get_label_column();
		$data_col = $widget->get_data_column();
		foreach ($ds->get_rows() as $nr => $row){
			$new_row_id = null;
			$new_row = array();
			$new_col_val = null;
			$new_col_id = null;
			foreach ($row as $fld => $val){
				
				if ($fld === $label_col->get_data_column_name()){
					$new_col_id = $val;
					// TODO we probably need a special parameter for sorting labels!
					if (!in_array($val, $this->label_values)) $this->label_values[] = $val;
				} elseif ($fld === $data_col->get_data_column_name()){
					$new_col_val = $val; 
				} elseif (in_array($fld, $visible_columns)) {
					$new_row_id .= $val;
					$new_row[$fld] = $val;
				}
			}
			if (!is_array($result[$new_row_id])){
				$result[$new_row_id] = $new_row;
			}
			$result[$new_row_id][$new_col_id] = $new_col_val;
		}
		
		$output = "data: [";
		foreach ($result as $row){
			$output .= "{";
			foreach ($row as $fld => $val){
				$output .= '"' . $this->clean_id($fld) . '": "' . str_replace('"', '\"', $val) .'",';
			}
			$output = substr($output, 0, -1);
			$output .= '},';
		}
		$output = substr($output, 0, -1);
		$output .= ']';
		return $output;
	}
	
	/**
	 * This special column renderer for the matrix replaces the column specified by label_column_id with a set of new columns for
	 * every unique value in the column specified by data_column_id. The new columns retain most properties of the replaced label column.
	 * @see \exface\JEasyUiTemplate\Template\Elements\grid::render_column_headers()
	 */
	public function render_column_headers(array $column_groups = null){
		$widget = $this->get_widget();
		$cols = $this->get_widget()->get_columns();
		$new_cols = $widget->get_page()->create_widget('DataColumnGroup', $widget);
		foreach ($cols as $id => $col){
			if ($col->get_id() === $this->get_widget()->get_data_column_id()){
				// replace the data column with a new set of columns for each possible label
				foreach ($this->label_values as $label){
					$new_col = clone($col);
					$new_col->set_data_column_name(DataColumn::sanitize_column_name($label));
					$new_col->set_caption($label);
					$new_col->set_sortable(false);
					$new_cols->add_column($new_col);
				}
			} elseif($col->get_id() === $this->get_widget()->get_label_column_id()) {
				// doing nothing here makes the label column disapear
			} else {
				$new_cols->add_column($col);
			}
		}
		
		return parent::render_column_headers(array($new_cols));
	}
}
?>