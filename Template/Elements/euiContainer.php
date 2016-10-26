<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiContainer extends euiAbstractElement {
	
	function generate_html(){
		return $this->children_generate_html();
	}
	
	function generate_js(){
		return $this->children_generate_js();
	}
	
	function children_generate_html(){
		$output = '';
		foreach ($this->get_widget()->get_children() as $subw){
			$output .= $this->get_template()->generate_html($subw) . "\n";
		};
		return $output;
	}
	
	function children_generate_js(){
		$output = '';
		foreach ($this->get_widget()->get_children() as $subw){
			$output .= $this->get_template()->generate_js($subw) . "\n";
		};
		return $output;
	}
	
	public function generate_widgets_html(){
		foreach ($this->get_widget()->get_widgets() as $subw){
			$output .= $this->get_template()->generate_html($subw) . "\n";
		};
		return $output;
	}
	
	public function generate_widgets_js(){
		foreach ($this->get_widget()->get_widgets() as $subw){
			$output .= $this->get_template()->generate_js($subw) . "\n";
		};
		return $output;
	}
	
	/**
	 * TODO The build_js_data_getter() should return an array with data from all widgets in the container. Recursive method needed here!
	 * @see \exface\JEasyUiTemplate\Template\Elements\euiAbstractElement::build_js_data_getter()
	 */
	public function build_js_data_getter($include_inactive_data = false){
		return '[]';
	}
}
?>