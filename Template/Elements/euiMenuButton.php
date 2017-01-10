<?php namespace exface\JEasyUiTemplate\Template\Elements;

use exface\AbstractAjaxTemplate\Template\Elements\JqueryButtonTrait;
use exface\Core\Widgets\Dialog;

/**
 * generates jEasyUI-Buttons for ExFace
 * @author Andrej Kabachnik
 *
 */
class euiMenuButton extends euiAbstractElement {
	
	use JqueryButtonTrait;
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::init()
	 */
	protected function init(){
		parent::init();
		$this->set_element_type('menubutton');
	}
	
	/**
	 * @see \exface\JEasyUiTemplate\Template\Elements\abstractWidget::generate_html()
	 */
	function generate_html(){
		$buttons_html = '';
		$output = '';
		
		foreach ($this->get_widget()->get_buttons() as $b){
			// If the button has an action, make some action specific HTML depending on the action
			if ($action = $b->get_action()){
				if ($action->implements_interface('iShowDialog')){
					$dialog_widget = $action->get_dialog_widget();
					$output .= $this->get_template()->generate_html($dialog_widget);
				}
			}
			// In any case, create a menu entry
			$icon = $b->get_icon_name() ? ' iconCls="'.$this->build_css_icon_class($b->get_icon_name()).'"' : '';
			$disabled = $b->is_disabled() ? ' disabled=true' : '';
			$buttons_html .=
				'<div'.$icon.$disabled.'>
					'.$b->get_caption().'
				</div>
				';
		}
		
		$icon = $this->get_widget()->get_icon_name() ? ',iconCls:\''.$this->build_css_icon_class($this->get_widget()->get_icon_name()).'\'' : '';
		$output .=
			'<a href="javascript:void(0)" id="'.$this->get_id().'" class="easyui-'.$this->get_element_type().'" data-options="menu:\'#'.$this->build_js_menu_name().'\''.$icon.'">
				'.$this->get_widget()->get_caption().'
			</a>
			<div id="'.$this->build_js_menu_name().'">
				'.$buttons_html.'
			</div>
			';
		
		if ($this->get_widget()->get_input_widget() instanceof Dialog && !$this->get_widget()->get_parent() instanceof Dialog) {
			// Hier wird versucht zu unterscheiden wo sich der Knopf befindet. Der Wrapper wird nur benoetigt
			// wenn er sich in einem Dialog befindet, aber nicht als Knopf im Footer, sondern im Inhalt.
			$output = $this->build_html_wrapper_div($output);
		}
		
		return $output;
	}
	
	/**
	 * 
	 */
	function build_html_button() {
		
	}
	
	/**
	 * 
	 * @param unknown $html
	 * @return string
	 */
	protected function build_html_wrapper_div($html){
		$output =
			'<div class="fitem exf_input" title="'.trim($this->build_hint_text()).'" style="width: '.$this->get_width().'; height: '.$this->get_height().';">
				'.$html.'
			</div>
			';
		return $output;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::generate_js()
	 */
	function generate_js(){
		$output = '';
		$output .=
			'$("#'.$this->build_js_menu_name().'").menu({
				onClick:function(item){
					switch(item.text) {
						';
		
		foreach ($this->get_widget()->get_buttons() as $b) {
			$output .=
						'case "'.$b->get_caption().'":
							'.$this->get_template()->get_element($b)->build_js_click_function().'
							break;
						';
		}
		$output .=
					'}
				}
			});';
		return $output;
	}
	
	/**
	 * 
	 * @return string
	 */
	function build_js_menu_name() {
		return $this->get_id().'_menu';
	}
}
?>