<?php namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\DialogButton;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryButtonTrait;
use exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement;
use exface\Core\Widgets\Button;
use exface\Core\Interfaces\WidgetInterface;

/**
 * generates jEasyUI-Buttons for ExFace
 * @author Andrej Kabachnik
 *
 */
class euiMenuButton extends euiAbstractElement {

	use JqueryButtonTrait;
	
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
			$buttons_html .=
				'<div'.$icon.'>
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
		
		return $this->build_html_wrapper_div($output);
	}
	
	protected function build_html_wrapper_div($html){
		$output =
			'<div class="fitem exf_input" title="'.trim($this->build_hint_text()).'" style="width: '.$this->get_width().'; height: '.$this->get_height().';">
				'.$html.'
			</div>
			';
		return $output;
	}
	
	function generate_js(){
		$output = '';
		$output .=
			'$("#'.$this->build_js_menu_name().'").menu({
				onClick:function(item){
					//alert(item.text);
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
	
	function build_js_menu_name() {
		return $this->get_id().'_menu';
	}
	
	function build_js_click_function_name() {
		return $this->build_js_function_prefix() . 'click';
	}
	
	function build_js_click_function() {
		return '';
	}
	
	protected function build_js_click_show_dialog(WidgetInterface $widget){
		$input_element = $this->get_template()->get_element($widget->get_input_widget(), $this->get_page_id());
		$action = $widget->get_action();
		$test = $action->get_input_data_sheet();
		/* @var $prefill_link \exface\Core\CommonLogic\WidgetLink */
		$prefill = '';
		//if ($prefill_link = $widget->get_action()->get_prefill_with_data_from_widget_link()){
		//	if ($prefill_link->get_page_id() == $widget->get_page_id()){
		//		$prefill = ", prefill: " . $this->get_template()->get_element($prefill_link->get_widget())->build_js_data_getter($this->get_action());
		//	}
		//}
		$output = $this->build_js_request_data_collector($action, $input_element);
		$output .= "
						" . $this->build_js_busy_icon_show() . "
						$.post('" . $this->get_ajax_url() ."',
							{
								action: '".$widget->get_action_alias()."',
								resource: '" . $widget->get_page_id() . "',
								element: '" . $widget->get_id() . "',
								object: '" . $widget->get_meta_object_id() . "',
								data: requestData
							},
							function(result) {
								var response = {};
								try {
									response = $.parseJSON(result);
								} catch (e) {
									response.error = result;
								}
			                   	if (response.success){
									" . $this->build_js_close_dialog($widget, $input_element) . "
									" . $this->build_js_input_refresh($widget, $input_element) . "
			                       	" . $this->build_js_busy_icon_hide() . "
									if (response.success || response.undoURL){
			                       		" . $this->build_js_show_success_message("response.success + (response.undoable ? ' <a href=\"" . $this->build_js_undo_url($action, $input_element) . "\" style=\"display:block; float:right;\">UNDO</a>' : '')") . "
									}
			                    } else {
									" . $this->build_js_busy_icon_hide() . "
									" . $this->build_js_show_error_message('response.error', 'Server error') . "
			                    }
							}
						);";
		
		return $output;
		/*$output = $this->build_js_request_data_collector($action, $input_element) . "
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
		return $output;*/
	}

	protected function build_js_close_dialog($widget, $input_element){
		return ($widget instanceof DialogButton && $widget->get_close_dialog_after_action_succeeds() ? "$('#" . $input_element->get_id() . "').dialog('close');" : "" );
	}

}
?>