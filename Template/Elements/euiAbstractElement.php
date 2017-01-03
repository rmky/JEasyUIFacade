<?php namespace exface\JEasyUiTemplate\Template\Elements;

use exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement;
use exface\JEasyUiTemplate\Template\JEasyUiTemplate;

abstract class euiAbstractElement extends AbstractJqueryElement {
	
	public function build_js_init_options(){
		return '';
	}
	
	public function build_js_inline_editor_init(){
		return '';
	}
	
	/**
	 * 
	 * @return JEasyUiTemplate
	 */
	public function get_template(){
		return parent::get_template();
	}
	
	public function escape_string($string){
		return str_replace('"', "'", $string);
	}
	
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
				
				// See if the formatter returned more results, than there were rows. If so, it was also performed on
				// the total rows. In this case, we need to slice them off and pass to set_column_values() separately.
				// This only works, because evaluating an expression cannot change the number of data rows! This justifies
				// the assumption, that any values after count_rows() must be total values.
				$vals = $formatter->evaluate($data_sheet, $name);
				if ($data_sheet->count_rows() < count($vals)) {
					$totals = array_slice($vals, $data_sheet->count_rows());
					$vals = array_slice($vals, 0, $data_sheet->count_rows());
				}
				$data_sheet->set_column_values($name, $vals, $totals);
			}
		}
		$data = array();
		$data['rows'] = $data_sheet->get_rows();
		$data['total'] = $data_sheet->count_rows_all();
		$data['footer'] = $data_sheet->get_totals_rows();
		return $data;
	}
	
	public function build_js_busy_icon_show(){
		return "$.messager.progress({});";
	}
	
	public function build_js_busy_icon_hide(){
		return "$.messager.progress('close');";
	}
	
	public function build_js_show_error_message($message_body_js, $title_js = null){
		$title_js = !is_null($title_js) ? $title_js : '"Error"';
		return 'jeasyui_create_dialog($("body"), "' . $this->get_id() . '_error", {title: ' . $title_js . ', width: 800, height: "80%"}, ' . $message_body_js . ', true);';
	}
	
	public function build_js_show_success_message($message_body_js, $title = null){
		$title = !is_null($title) ? $title : 'Error';
		return "$.messager.show({
					title: '" . $title . "',
	                msg: " . $message_body_js . ",
	                timeout:5000,
	                showType:'slide'
	            });";
	}
}
?>