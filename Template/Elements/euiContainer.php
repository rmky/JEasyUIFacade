<?php
namespace exface\JEasyUiTemplate\Template\Elements;
use exface\Core\Interfaces\Actions\ActionInterface;

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
	 * 
	 * {@inheritDoc}
	 * @see \exface\JEasyUiTemplate\Template\Elements\euiAbstractElement::build_js_data_getter()
	 */
	public function build_js_data_getter(ActionInterface $action = null, $custom_body_js = null){
		$output = 'data.rows = []' . "\n";
		$found_inputs = false;
		foreach ($this->get_widget()->get_children_recursive() as $child){
			if ($child->implements_interface('iTakeInput')){
				if (!$found_inputs){
					$output .= 'data.rows[0] = {};';
					$found_inputs = true;
				}
				$output .= 'data.rows[0]["' . $child->get_attribute_alias() . '"] = ' . $this->get_template()->get_element($child)->build_js_value_getter() . ";\n";
			}
		}
		return parent::build_js_data_getter($action, $output . $custom_body_js);		
	}
}
?>