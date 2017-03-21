<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiFilter extends euiInput {
	
	protected function init(){
		// Ueberschrieben, denn sowohl der Filter, als auch die zugehoerige ComboTable
		// wurden als Live-Referenz registriert. Da der Filter aber nur an die ComboTable
		// weiterleitet, kam es zu doppeltem Code.
		$this->set_element_type('textbox');
	}
	
	function generate_html(){
		return $this->get_template()->get_element($this->get_widget()->get_widget())->generate_html();
	}
	
	function generate_js(){
		return $this->get_template()->get_element($this->get_widget()->get_widget())->generate_js();
	}
	
	function build_js_value_getter(){
		return $this->get_template()->get_element($this->get_widget()->get_widget())->build_js_value_getter();
	}
	
	function build_js_value_getter_method(){
		return $this->get_template()->get_element($this->get_widget()->get_widget())->build_js_value_getter_method();
	}
	
	function build_js_value_setter($value){
		return $this->get_template()->get_element($this->get_widget()->get_widget())->build_js_value_setter($value);
	}
	
	function build_js_value_setter_method($value){
		return $this->get_template()->get_element($this->get_widget()->get_widget())->build_js_value_setter_method($value);
	}
	
	function build_js_init_options(){
		return $this->get_template()->get_element($this->get_widget()->get_widget())->build_js_init_options();
	}
	
	/**
	 * Magic method to forward all calls to methods, not explicitly defined in the filter to ist value widget.
	 * Thus, the filter is a simple proxy from the point of view of the template. However, it can be easily
	 * enhanced with additional methods, that will override the ones of the value widget.
	 * TODO this did not really work so far. Don't know why. As a work around, added some explicit proxy methods
	 * -> __call wird aufgerufen, wenn eine un!zugreifbare Methode in einem Objekt aufgerufen wird
	 * (werden die ueberschriebenen Proxymethoden entfernt, existieren sie ja aber auch noch euiInput)
	 * @param string $name
	 * @param array $arguments
	 */
	public function __call($name, $arguments){
		return call_user_method_array($name, $this->get_template()->get_element($this->get_widget()->get_widget()), $arguments);
	}
}
?>