<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiDataTree extends euiDataTable {
	private $on_expand = '';
	
	protected function init(){
		parent::init();
		$this->set_element_type('treegrid');
	}
	
	public function render_grid_head(){
		if ($this->is_editable()){
			$this->add_on_expand('
					if (row){
						var rows = $(this).' . $this->get_element_type() . '("getChildren", row.' . $this->get_widget()->get_uid_column()->get_data_column_name() . ');
						for (var i=0; i<rows.length; i++){
							$(this).' . $this->get_element_type() . '("beginEdit", rows[i].' . $this->get_widget()->get_uid_column()->get_data_column_name() . ');
						}
					}
					');
		}
		$grid_head = parent::render_grid_head()
			. ', treeField: "' . $this->get_widget()->get_tree_column()->get_data_column_name() . '"'
			. ($this->get_on_expand() ? ', onExpand: function(row){' . $this->get_on_expand() . '}' : '');
		return $grid_head;
	}
	
	public function prepare_data(\exface\Core\Interfaces\DataSheets\DataSheetInterface $data_sheet){
		$result = parent::prepare_data($data_sheet);
		/* @var $widget \exface\Core\Widgets\DataTree */
		$widget = $this->get_widget();
		foreach ($result['rows'] as $nr => $row){
			if ($row[$widget->get_tree_folder_flag_attribute_alias()]){
				// $result['rows'][$nr]['state'] = $row[$this->get_widget()->get_tree_folder_flag_attribute_alias()] ? 'closed' : 'open';
				$result['rows'][$nr]['state'] = 'closed';
				// Dirty hack to remove zero numeric values on folders, because they are easily assumed to be sums
				foreach ($row as $fld => $val){
					if (is_numeric($val) && intval($val) == 0){
						$result['rows'][$nr][$fld] = '';
					}
				}
			} else {
				$result['rows'][$nr]['state'] = 'open';
			}
			
			unset ($result['rows'][$nr][$this->get_widget()->get_tree_folder_flag_attribute_alias()]);
			if ($result['rows'][$nr][$widget->get_tree_parent_id_attribute_alias()] != $widget->get_tree_root_uid()){
				$result['rows'][$nr]['_parentId'] = $result['rows'][$nr][$widget->get_tree_parent_id_attribute_alias()];
			}
			
		}
		
		return $result;
	}
	
	public function build_js_edit_mode_enabler(){
		return '
					var rows = $(this).' . $this->get_element_type() . '("getRoots");
					for (var i=0; i<rows.length; i++){
						$(this).' . $this->get_element_type() . '("beginEdit", rows[i].' . $this->get_widget()->get_uid_column()->get_data_column_name() . ');
					}
				';
	}
	
	public function add_on_expand($script){
		$this->on_expand .= $script;
	}
	
	public function get_on_expand(){
		return $this->on_expand;
	}
}
?>