<?php namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\DialogButton;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryButtonTrait;
use exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement;

/**
 * generates jEasyUI-Buttons for ExFace
 * @author Andrej Kabachnik
 *
 */
class euiButton extends euiAbstractElement {
	
	use JqueryButtonTrait;
	
	function generate_js(){
		$output = '';
		$action = $this->get_action();
		
		// Generate helper functions, that do not depend on the action
		
		// Get the click function for the button. This might also be required for buttons without actions
		if ($click = $this->build_js_click_function()) {
			// Generate the function to be called, when the button is clicked
			$output .= "
				function " . $this->build_js_click_function_name() . "(){
					" . $click . "
				}";
		}
		
		// Get the java script required for the action itself
		if ($action){
			// Actions with template scripts may contain some helper functions or global variables.
			// Print the here first.
			if ($action && $action->implements_interface('iRunTemplateScript')){
				$output .= $this->get_action()->print_helper_functions();
			}
			// See if the action needs some more JS, that is not the click function (e.g. showing another widget)
			if ($action->implements_interface('iShowDialog')){
				$dialog_widget = $action->get_dialog_widget();
				$output .= $this->get_template()->generate_js($dialog_widget);
			}
		}
		
		return $output;
	}
	
	/**
	 * @see \exface\JEasyUiTemplate\Template\Elements\abstractWidget::generate_html()
	 */
	function generate_html(){
		$action = $this->get_action();
		
		// If the button has an action, make some action specific HTML depending on the action
		if ($action){
			if ($action->implements_interface('iShowDialog')){
				$dialog_widget = $action->get_dialog_widget();
				$output .= $this->get_template()->generate_html($dialog_widget);
			} 
		}
		
		// In any case, create a linkbutton
		$output .= $this->build_html_button();
		
		return $output;
	}
	
	public function build_html_button(){
		$output = '
				<a id="' . $this->get_id() . '" href="javascript:;" plain="true" class="easyui-linkbutton" iconCls="' . $this->build_css_icon_class($this->get_widget()->get_icon_name()) . '" onclick="' . $this->build_js_function_prefix() . 'click();">
						' . $this->get_widget()->get_caption() . '
				</a>';
		return $output;
	}
	
	protected function build_js_click_show_dialog(ActionInterface $action, AbstractJqueryElement $input_element){
		$widget = $this->get_widget();
		/* @var $prefill_link \exface\Core\CommonLogic\WidgetLink */
		$prefill = '';
		if ($prefill_link = $this->get_action()->get_prefill_with_data_from_widget_link()){
			if ($prefill_link->get_page_id() == $widget->get_page_id()){
				$prefill = ", prefill: " . $this->get_template()->get_element($prefill_link->get_widget())->build_js_data_getter($this->get_action());
			}
		}
		return $this->build_js_request_data_collector($action, $input_element) . "
					$('#" . $this->get_id($action->get_dialog_widget()->get_id()) . "').dialog({
							href: '" . $this->get_ajax_url() . "',
							method: 'post',
							queryParams: {
								resource: '".$widget->get_page_id()."',
								element: '".$widget->get_id()."',
								action: '".$widget->get_action_alias()."',
								data: requestData
								" . $prefill . "
							}
							" . ($this->build_js_input_refresh($widget, $input_element) ? ", onBeforeClose: function(){" . $this->build_js_input_refresh($widget, $input_element) . ";}" : "") . "
						});
					" . $this->build_js_close_dialog($widget, $input_element) . "
					$('#" . $this->get_id($action->get_dialog_widget()->get_id()) . "').dialog('open').dialog('setTitle','" . $widget->get_caption() . "');";
	}
	
	protected function build_js_close_dialog($widget, $input_element){
		return ($widget instanceof DialogButton && $widget->get_close_dialog_after_action_succeeds() ? "$('#" . $input_element->get_id() . "').dialog('close');" : "" );
	}
	
}
?>