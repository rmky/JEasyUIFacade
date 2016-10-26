<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiComboTable extends euiInput {
	
	function init(){
		parent::init();
		$this->set_element_type('combogrid');
	}
	
	function generate_html(){
		$widget = $this->get_widget();
		$output = '	<input style="height: 100%;width:100%;" id="' . $this->get_id() . '" 
							name="' . $widget->get_attribute_alias() . ($widget->get_multi_select() ? '[]' : '') . '" 
							value="' . $this->get_value_with_defaults() . '" />
					';
		
		return $this->build_html_wrapper_div($output);
	}
	
	function generate_js(){
		// Need to understand, if it's the first time loading to prevent that loading if a value is set already
		$output .= '$("#' . $this->get_id() . '").combogrid({';
		$output .= $this->build_js_init_options();
		$output .= '});';
		// Add a clear icon to each combo grid - a small cross to the right, that resets the value
		// TODO The addClearBtn extension seems to break the setText method, so that it also sets the value. Perhaps we can find a better way some time
		// $output .= "$('#" . $this->get_id() . "').combogrid('addClearBtn', 'icon-clear');";
		return $output;
	}
	
	function build_js_init_options(){
		/* @var $widget \exface\Core\Widgets\ComboTable */
		$widget = $this->get_widget();
		/* @var $table \exface\JEasyUiTemplate\Template\Elements\DataTable */
		$table = $this->get_template()->get_element($widget->get_table());
		
		// Prevent loading data from backend if the value and value_text are set already or there is
		// no value and thus no need to search for anything.
		// The trouble here is, that if the first loading is prevented, the next time the user clicks on the dropdown button,
		// an empty table will be shown, because the last result is cached. To fix this, we bind a reload of the table to
		// onShowPanel in case the grid is empty (see below).
		if (!is_null($this->get_value_with_defaults()) && $this->get_value_with_defaults() !== ''){
			if ($widget->get_value_text()){
				// If the text is already known, set it an prevent initial backend request
				$first_load_script = "$('#" . $this->get_id() ."')." . $this->get_element_type() . '("setText", "' . str_replace('"', '\"', $widget->get_value_text()) . '"); return false;';
			} else {
				// If there is a value, but no text, add a filter over the UID column with this value and do not prevent the initial autoload
				$first_load_script = "param.fltr01_" . $widget->get_value_column()->get_data_column_name() . " = '" . $this->get_value_with_defaults() . "';";
			}
		} else {
			// If no value set, just supress initial autoload
			$first_load_script = "return false;";
		}
		
		$table->add_on_before_load("
			if ($('#" . $this->get_id() . "')." . $this->get_element_type() . "('options').firstLoad){
				$('#" . $this->get_id() . "')." . $this->get_element_type() . "('options').firstLoad = false;
				" . $first_load_script . "
			} else {
				if (!param.q){
					param.q = $('#" . $this->get_id() . "')." . $this->get_element_type() . "('getText');
				}
			}
		");
		
		// Add explicitly specified values to every return data
		foreach ($widget->get_selectable_options() as $key => $value){
			if ($key === '' || is_null($key)) continue;
			$table->add_load_filter_script('data.rows.unshift({' . $widget->get_table()->get_uid_column()->get_data_column_name() . ': "' . $key . '", ' . $widget->get_text_column()->get_data_column_name() . ': "' . $value . '"});');
		}
		
		// Init the combogrid itself.
		$output .= $table->render_grid_head();
		$output .= '
						, textField:"' . $this->get_widget()->get_text_column()->get_data_column_name() . '"
						, mode: "remote"
						, method: "post"
						, delay: 600
						, panelWidth:600
						, firstLoad: true
						' . ($widget->is_required() ? ', required:true' : '') . '
						' . ($widget->is_disabled() ? ', disabled:true' : '') . '
						' . ($widget->get_multi_select() ? ', multiple: true' : '') . '
						' . ($this->get_on_change_script() ? ', onChange: function(){' . $this->get_on_change_script() . '}' : '') . '
						, onShowPanel: function() {
							if($(this).combogrid("grid").datagrid("getRows").length == 0) {
								$(this).combogrid("grid").datagrid("reload");
			                }
						}';
		return $output;
	}
	
	function build_js_value_getter($column = null, $row = null){
		if ($this->get_widget()->get_multi_select()){
			return '$("#' . $this->get_id() . '").combogrid("getValues").join()';
		} else {
			if (!is_null($column) && $column !== ''){
				return 'function(){var row = $("#' . $this->get_id() . '").combogrid("grid").datagrid("getSelected"); if(row) {return row["' . $column . '"]} else {return ""}}';
			}
			return '$("#' . $this->get_id() . '").combogrid("getValue")';
		}
	}
}
?>