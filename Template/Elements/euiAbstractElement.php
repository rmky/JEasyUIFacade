<?php namespace exface\JEasyUiTemplate\Template\Elements;

use exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement;
use exface\JEasyUiTemplate\Template\JEasyUiTemplate;

abstract class euiAbstractElement extends AbstractJqueryElement {
	protected $element_id_forbidden_chars = array('/', '(', ')', '.');
	protected $function_prefix_forbidden_chars = array('-', '.');
	
	protected $ajax_url = 'exface/exface.php?exftpl=exface.JEasyUiTemplate';
	protected $element_type;
	
	private $icon_classes = array(
			'edit' => 'icon-edit',
			'remove' => 'icon-remove',
			'add' => 'icon-add',
			'save' => 'icon-save',
			'cancel' => 'icon-cancel'
	);
	
	function build_js_init_options(){
		return '';
	}
	
	function build_js_inline_editor_init(){
		return '';
	}
	
	function get_function_prefix(){
		return str_replace($this->function_prefix_forbidden_chars, '_', $this->get_id()) . '_';
	}
	
	/**
	 * Returns the id of the HTML-element representing the widget
	 * @return string
	 */
	function get_id($exf_widget_id = null){
		$widget_id = ($exf_widget_id ? $exf_widget_id : $this->get_widget()->get_id());
		return  $this->clean_id($widget_id) . ($this->get_template()->get_workbench()->get_request_id() ? '-' . $this->get_template()->get_workbench()->get_request_id() : '');
	}
	
	/**
	 * Replaces all characters, which are not supported in the ids of DOM-elements (i.e. "/" etc.)
	 * TODO If widgets are used for input, cleaning the id of usupported characters will probably not be enough.
	 * Bidirectional masking needs to be implemented
	 */
	function clean_id($id){
		return str_replace($this->element_id_forbidden_chars, '_', $id);
	}
	
	/**
	 * 
	 * @return JEasyUiTemplate
	 */
	function get_template(){
		return parent::get_template();
	}
	
	function escape_string($string){
		return str_replace('"', "'", $string);
	}

	public function get_ajax_url() {
		$request_id = $this->get_template()->get_workbench()->get_request_id();
		return $this->ajax_url . ($request_id ? '&exfrid=' . $request_id : '');
	}
	
	public function set_ajax_url($value) {
		$this->ajax_url = $value;
	}
	
	public function get_element_type() {
		return $this->element_type;
	}
	
	public function set_element_type($value) {
		$this->element_type = $value;
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
	
	public function get_icon_class($exf_icon_name){
		if ($this->icon_classes[$exf_icon_name]){
			return $this->icon_classes[$exf_icon_name];
		} else {
			return 'icon-' . $exf_icon_name;
		}
	}
	
	public function build_js_busy_icon_show(){
		return "$.messager.progress({});";
	}
	
	public function build_js_busy_icon_hide(){
		return "$.messager.progress('close');";
	}
}
?>