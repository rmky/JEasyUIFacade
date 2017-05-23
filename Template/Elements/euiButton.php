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
		}
		
		return $output;
	}
	
	/**
	 * @see \exface\JEasyUiTemplate\Template\Elements\abstractWidget::generate_html()
	 */
	function generate_html(){
		// Create a linkbutton
		$output .= $this->build_html_button();
		
		return $output;
	}
	
	public function build_html_button(){
		$widget = $this->get_widget();
		
		$style = '';
		switch ($widget->get_align()){
			case EXF_ALIGN_LEFT: $style .= 'float: left;'; break;
			case EXF_ALIGN_RIGHT: $style .= 'float: right;'; break;
		}
		
		$data_options = '';
		if ($widget->get_visibility() != EXF_WIDGET_VISIBILITY_PROMOTED){
			$data_options .= 'plain: true';
		} else {
			$data_options .= 'plain: false';
		}
		if ($widget->is_disabled()) {
			$data_options .= ', disabled: true';
		}
		if ($widget->get_icon_name()) {
			$data_options .= ", iconCls: '" . $this->build_css_icon_class($widget->get_icon_name()) . "'";
		}
		
		$output = '
				<a id="' . $this->get_id() . '" title="'. str_replace('"', '\"', $widget->get_caption()) . '" href="javascript:;" class="easyui-linkbutton" data-options="' . $data_options . '" style="' . $style . '" onclick="' . $this->build_js_function_prefix() . 'click();">
						' . $widget->get_caption() . '
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
		
		$output = $this->build_js_request_data_collector($action, $input_element);
		$output .= <<<JS
						{$this->build_js_busy_icon_show()}
						$.ajax({
							type: 'POST',
							url: '{$this->get_ajax_url()}',
							dataType: 'html',
							data: {
								action: '{$widget->get_action_alias()}',
								resource: '{$widget->get_page_id()}',
								element: '{$widget->get_id()}',
								data: requestData
								{$prefill}
							},
							success: function(data, textStatus, jqXHR) {
								{$this->build_js_close_dialog($widget, $input_element)}
		                       	if ($('#ajax-dialogs').length < 1){
		                       		$('body').append('<div id=\"ajax-dialogs\"></div>');
                       			}
								$('#ajax-dialogs').append('<div class=\"ajax-wrapper\">'+data+'</div>');
								var dialogId = $('#ajax-dialogs').children().last().children('.easyui-dialog').attr('id');
		                       	$.parser.parse($('#ajax-dialogs').children().last());
								var onCloseFunc = $('#'+dialogId).panel('options').onClose;
								$('#'+dialogId).panel('options').onClose = function(){
									onCloseFunc();
									
									// MenuButtons manuell zerstoeren, um Ueberbleibsel im body zu verhindern
									var menubuttons = $('#'+dialogId).parent().find('.easyui-menubutton');
									for (i = 0; i < menubuttons.length; i++) {
										$(menubuttons[i]).menubutton('destroy');
									}
									
									$(this).dialog('destroy').remove(); 
									$('#ajax-dialogs').children().last().remove();
									{$this->build_js_input_refresh($widget, $input_element)}
								};
                       			$(document).trigger('{$action->get_alias_with_namespace()}.action.performed', [requestData]);
                       			{$this->build_js_busy_icon_hide()}
							},
							error: function(jqXHR, textStatus, errorThrown){
								{$this->build_js_show_error('jqXHR.responseText', 'jqXHR.status + " " + jqXHR.statusText')}
								{$this->build_js_busy_icon_hide()}
							}
						});
						{$this->build_js_close_dialog($widget, $input_element)} 
JS;
		return $output;
	}
	
	protected function build_js_close_dialog($widget, $input_element){
		return ($widget instanceof DialogButton && $widget->get_close_dialog_after_action_succeeds() ? "$('#" . $input_element->get_id() . "').dialog('close');" : "" );
	}	
	
	/**
	 * In jEasyUI the button does not need any extra headers, as all headers needed for whatever the button loads will
	 * come with the AJAX-request.
	 *
	 * {@inheritDoc}
	 * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::generate_headers()
	 */
	public function generate_headers(){
		return array();
	}
}
?>