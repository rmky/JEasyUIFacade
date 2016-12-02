<?php namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Interfaces\Actions\ActionInterface;

class euiDiagramShapeData extends euiAbstractElement {
	function generate_html(){
		return '';
	}
	
	function generate_js(){
		return '';
	}
	
	public function build_js_data_getter(ActionInterface $action = null, $custom_body_js = null){
		if ($action){
			$custom_body_js = $custom_body_js . "data.rows = [{'" . $this->get_meta_object()->get_uid_alias() . "': " . $this->build_js_value_getter() . "}]";
		}
		return parent::build_js_data_getter($action, $custom_body_js);
	}
	
	public function build_js_value_getter(){
		$js = $this->get_template()->get_element($this->get_widget()->get_diagram())->get_id() . "_selected.data('oid')";
		return $js;
	}
	
	public function build_js_refresh(){
		return $this->get_template()->get_element($this->get_widget()->get_diagram())->build_js_refresh();
	}
}
?>