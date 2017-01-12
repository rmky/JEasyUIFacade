<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiPivotTable extends euiDataTable {
	private $label_values = array();
	
	protected function init(){
		parent::init();
		$this->set_element_type('pivotgrid');
		$this->add_on_before_load('if (!$("#' . $this->get_id() . '").data("layouted")) {$("#' . $this->get_id() . '").data("layouted", 1)}');
	}
	
	function generate_js(){
		$widget = $this->get_widget();
		$output = '';
		
		// Prevent loading data again every time the pivot layout changes. The layout still works with the same data, so why load it again?
		// TODO This simple approach did not work, because the layout is not refreshed then. Need another approach somehow.
		/*$this->add_on_before_load("
						console.log($(this).treegrid('getData'));
							if ($(this).treegrid('getData').length > 0) return false;
						");*/
		
		// add initial sorters
		$sort_by = array();
		$direction = array();
		if (count($widget->get_sorters()) > 0){
			foreach ($widget->get_sorters() as $sort){
				$sort_by[] = urlencode($sort->attribute_alias);
				$direction[] = urlencode($sort->direction);
			}
			$sortColumn = ", sortName: '" . implode(',', $sort_by) . "'";
			$sortOrder = ", sortOrder: '" . implode(',', $direction) . "'";
		}
		
		// get the standard params for grids
		$grid_head = $this->build_js_data_source();
		$grid_head .=  $sortColumn . $sortOrder .
						($this->get_on_before_load() ? ', onBeforeLoad: function(param){' . $this->get_on_before_load() . '}' : '') . '
						, toolbar:[ {
					        text:\'Layout\',
					        handler:function(){
					            $(\'#' . $this->get_id() . '\').pivotgrid(\'layout\');
					        }
					    } ]
					    , fit: true
						, pivot: {rows: [], columns: [], values: []}';
		
		// instantiate the data grid
		$output .= '$("#' . $this->get_id() . '").' . $this->get_element_type() . '({' . $grid_head . '});';
		
		return $output;
	}
	
	function generate_headers(){
		$headers = parent::generate_headers();
		$headers[] = '<script type="text/javascript" src="exface/vendor/exface/JEasyUiTemplate/Template/js/jeasyui/extensions/pivotgrid/jquery.pivotgrid.js"></script>';
		return $headers;
	}
	
	/**
	 * A pivotGrid expects data in a different format: [ {field: value, ...}, {...}, ... ]
	 * @see \exface\JEasyUiTemplate\Template\Elements\jeasyuiAbstractWidget::prepare_data()
	 */
	public function prepare_data(\exface\Core\Interfaces\DataSheets\DataSheetInterface $data_sheet){
		// apply the formatters
		foreach ($data_sheet->get_columns() as $name => $col){
			if ($formatter = $col->get_formatter()) {
				$expr = $formatter->to_string();
				$function = substr($expr, 1, strpos($expr, '(')-1);
				$formatter_class_name = 'formatters\'' . $function;
				if (class_exists($class_name)){
					$formatter = new $class_name($y);
				}
				$data_sheet->set_column_values($name, $formatter->evaluate($data_sheet, $name));
			}
		}
		$data = array();
		foreach ($data_sheet->get_rows() as $row_nr => $row){
			foreach ($row as $fld => $val){
				if ($col = $this->get_widget()->get_column_by_data_column_name($fld)){
					$data[$row_nr][$col->get_caption()] = $val;
				}
			}
		} 
		return $data;
	}
	
	public function build_js_data_source(){
		$result = parent::build_js_data_source();
		
		$result = substr($result, 0, -1);
		//$result .= ', fltr99_' . $this->get_meta_object()->get_uid_alias() . ': ($("#' . $this->get_id() . '").data("layouted") ? "" : -1)}'; 
		$result .= ', page: (!$("#' . $this->get_id() . '").data("layouted") ? "" : 1), rows: (!$("#' . $this->get_id() . '").data("layouted") ? "" : 1)}';
		
		return $result;
	}
}
?>