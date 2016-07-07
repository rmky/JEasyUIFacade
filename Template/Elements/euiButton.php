<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Interfaces\Actions\iModifyData;
use exface\Core\Widgets\DialogButton;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\Actions\SaveData;
/**
 * generates jEasyUI-Buttons for ExFace
 * @author aka
 *
 */
class euiButton extends euiAbstractElement {
	
	public function generate_js_click_function_name(){
		return $this->get_function_prefix() . "click";
	}
	
	function generate_js(){
		$output = '';
		$action = $this->get_action();
		
		// Generate helper functions, that do not depend on the action
		
		// Get the click function for the button. This might also be required for buttons without actions
		if ($click = $this->generate_js_click_function()) {
			// Generate the function to be called, when the button is clicked
			$output .= "
				function " . $this->generate_js_click_function_name() . "(){
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
		$output .= $this->generate_html_button();
		
		return $output;
	}
	
	function generate_html_button(){
		$output = '
				<a id="' . $this->get_id() . '" href="javascript:;" plain="true" class="easyui-linkbutton" iconCls="' . $this->get_icon_class($this->get_widget()->get_icon_name()) . '" onclick="' . $this->get_function_prefix() . 'click();">
						' . $this->get_widget()->get_caption() . '
				</a>';
		return $output;
	}
	
	function generate_js_click_function(){
		$output = '';
		/* @var $widget \exface\Core\Widgets\Button */
		$widget = $this->get_widget();
		$input_element = $this->get_template()->get_element($widget->get_input_widget(), $this->get_page_id());
		
		$action = $widget->get_action();
		
		// if the button does not have a action attached, just see if the attributes of the button
		// will cause some click-behaviour and return the JS for that
		if (!$action) {
			$output .= $this->generate_js_close_dialog($widget, $input_element)
					. $this->generate_js_input_refresh($widget, $input_element);
			return $output;	
		}
		
		// Determine, how many input rows the action expects and generate a js checker for the action
		// Where the rows come from is not important at this point. We check the number of rows in the
		// resulting requestData, that will be populted based on the input widget type. Thus, this checker
		// does not depend on the widget, that deliveres the input data for the button
		if ($action->get_input_rows_min() || !is_null($action->get_input_rows_max())){
			if ($action->get_input_rows_min() === $action->get_input_rows_max()){
				$js_check_input_rows = "if (requestData.rows.length < " . $action->get_input_rows_min() . " || requestData.rows.length > " . $action->get_input_rows_max() . ") {alert('Please select exactly " . $action->get_input_rows_min() . " row(s)!'); return false;}";
			} elseif (is_null($action->get_input_rows_max())){
				$js_check_input_rows = "if (requestData.rows.length < " . $action->get_input_rows_min() . ") {alert('Please select at least " . $action->get_input_rows_min() . " row(s)!'); return false;}";
			} elseif (is_null($action->get_input_rows_min())){
				$js_check_input_rows = "if (requestData.rows.length > " . $action->get_input_rows_max() . ") {alert('Please select at most " . $action->get_input_rows_max() . " row(s)!'); return false;}";
			} else {
				$js_check_input_rows = "if (requestData.rows.length < " . $action->get_input_rows_min() . " || requestData.rows.length > " . $action->get_input_rows_max() . ") {alert('Please select from " . $action->get_input_rows_min() . " to " . $action->get_input_rows_max() . " rows first!'); return false;}";
			}
		} else {
			$js_check_input_rows = '';
		}
		
		if ($action->is_undoable()){
			$undo_url = $this->get_ajax_url() . "&action=exface.Core.UndoAction&resource=".$widget->get_page_id()."&element=".$widget->get_id();
		}
		
		// Create and populate the requestData JS-object
		// TODO make this a common JS-funktion for all buttons instead of including it to every buttons code
		$js_requestData = "
					var requestData = {};
					requestData.oId = '" . $widget->get_meta_object_id() . "';
					requestData.rows = " . $input_element->get_js_data_getter() . ";
					" . $js_check_input_rows; 
		
		// Generate the button specific JS code
		// IDEA there are many similarities here. Perhaps it is possible to make less elseifs...
		if ($action->implements_interface('iRunTemplateScript')){
			$output = $action->print_script($input_element->get_id());
		} elseif ($action->implements_interface('iShowDialog')) {
			// FIXME the request should be sent via POST to avoid length limitations of GET, however it does not seem to work with jEasyUI 1.3.6 (bug???)
			$output = $js_requestData . "
					$('#" . $this->get_id($action->get_dialog_widget()->get_id()) . "').dialog({
							href: '" . $this->get_ajax_url() . "',
							method: 'post',
							queryParams: {
								resource: '".$widget->get_page_id()."',
								element: '".$widget->get_id()."',
								action: '".$widget->get_action_alias()."',
								data: requestData		
							}
							" . ($this->generate_js_input_refresh($widget, $input_element) ? ", onBeforeClose: function(){" . $this->generate_js_input_refresh($widget, $input_element) . ";}" : "") . "
						});
					$('#" . $this->get_id($action->get_dialog_widget()->get_id()) . "').dialog('open').dialog('setTitle','" . $widget->get_caption() . "');";
		} elseif ($action->implements_interface('iShowWidget')) {
			/* @var $action \exface\Core\Interfaces\Actions\iShowWidget */
			if ($action->get_page_id() != $this->get_page_id()){
				$output = $js_requestData . "
				 	window.location.href = '" . $this->get_template()->create_link_internal($action->get_page_id()) . "?prefill={\"meta_object_id\":\"" . $widget->get_meta_object_id() . "\",\"rows\":[{\"" . $widget->get_meta_object()->get_uid_alias() . "\":\"' + requestData.rows[0]." . $widget->get_meta_object()->get_uid_alias() . " + '\"}]}';";
			}
		} elseif ($action->implements_interface('iShowUrl')) {
			/* @var $action \exface\Core\Interfaces\Actions\iShowUrl */
			$output = $js_requestData . "
					var " . $action->get_alias() . "Url='" . $action->get_url() . "';
					" . $this->generate_js_placeholder_replacer($action->get_alias() . "Url", "requestData.rows[0]", $action->get_url(), ($action->get_urlencode_placeholders() ? 'encodeURIComponent' : null));
			if ($action->get_open_in_new_window()){
				$output .= "window.open(" . $action->get_alias() . "Url);";
			} else {
				$output .= "window.location.href = " . $action->get_alias() . "Url;";
			}
		} elseif ($action->implements_interface('iModifyData') && $input_element->get_widget()->get_widget_type() != "DataTable") {
			$output = " var form = $('#" . $input_element->get_id() . " form');
						" . $this->get_js_busy_icon_show() . "
						form.attr('method', 'post');
						form.form('submit',{
			                success: function(result){
								var response = {};
								try {
									response = $.parseJSON(result);
								} catch (e) {
									response.error = result;
								}
			                   	if (response.success){
									" . $this->generate_js_close_dialog($widget, $input_element) . "
			                       	" . $this->get_js_busy_icon_hide() . "
									if (response.success || response.undoURL){
										$.messager.show({
											title: 'Success',
							                msg: response.success + (response.undoable ? ' <a href=\"" . $undo_url . "\" style=\"display:block; float:right;\">UNDO</a>' : ''),
							                timeout:5000,
							                showType:'slide'
							            });
									}
			                    } else {
									" . $this->get_js_busy_icon_hide() . "
			                        $.messager.alert({
			                            title: 'Error',
			                            msg: response.error
			                        });
			                    }
			                },
			                url: '" . $this->get_ajax_url() . "&resource=".$widget->get_page_id()."&element=".$widget->get_id()."&action=".$widget->get_action_alias() . "&object=" . $widget->get_meta_object_id() . "'
			            });";
		} else {
			$output = $js_requestData;
			if ($input_element->get_widget()->get_widget_type() == 'DataTable'){
				if ($input_element->is_editable() && $action instanceof SaveData){
					$output .= "
							requestData.rows = " . $input_element->get_js_changes_getter() . ";";
				}
			}
			$output .= "
						" . $this->get_js_busy_icon_show() . "
						$.post('" . $this->get_ajax_url() ."',
							{	resource: '" . $widget->get_page_id() . "',
								element: '" . $widget->get_id() . "',
								action: '".$widget->get_action_alias()."',
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
									" . $this->generate_js_input_refresh($widget, $input_element) . "
			                       	" . $this->get_js_busy_icon_hide() . "
									if (response.success || response.undoURL){
										$.messager.show({
											title: 'Success',
							                msg: response.success + (response.undoable ? ' <a href=\"" . $undo_url . "\" style=\"display:block; float:right;\">UNDO</a>' : ''),
							                timeout:5000,
							                showType:'slide'
							            });
									}
			                    } else {
									" . $this->get_js_busy_icon_hide() . "
			                        $.messager.alert({
			                            title: 'Error',
			                            msg: response.error
			                        });
			                    }
							}
						);";
		}
	
		return $output;
		
	}
	
	/**
	 * @return ActionInterface
	 */
	private function get_action(){
		return $this->get_widget()->get_action();
	}
	
	protected function generate_js_input_refresh($widget, $input_element){
		return ($widget->get_refresh_input() && $input_element->get_js_refresh() ? $input_element->get_js_refresh() . ";" : "");
	}
	
	protected function generate_js_close_dialog($widget, $input_element){
		return ($widget instanceof DialogButton && $widget->get_close_dialog_after_action_succeeds() ? "$('#" . $input_element->get_id() . "').dialog('close');" : "" );
	}
	
	/**
	 * Returns a javascript snippet, that replaces all placholders in a give string by values from a given javascript object.
	 * Placeholders must be in the general ExFace syntax [#placholder#], while the value object must have a property for every
	 * placeholder with the same name (without "[#" and "#]"!).
	 * @param string $js_var - e.g. result (the variable must be already instantiated!)
	 * @param string $js_values_array - e.g. values = {placeholder = "someId"}
	 * @param string $string_with_placeholders - e.g. http://localhost/pages/[#placeholder#]
	 * @param string $js_sanitizer_function - a Javascript function to be applied to each value (e.g. encodeURIComponent) - without braces!!!
	 * @return string - e.g. result = result.replace('[#placeholder#]', values['placeholder']);
	 */
	protected function generate_js_placeholder_replacer($js_var, $js_values_object, $string_with_placeholders, $js_sanitizer_function = null){
		$output = '';
		$placeholders = $this->get_template()->exface()->utils()->find_placeholders_in_string($string_with_placeholders);
		foreach ($placeholders as $ph){
			$value = $js_values_object . "['" . $ph . "']";
			if ($js_sanitizer_function){
				$value = $js_sanitizer_function . '(' . $value . ')';
			}
			$output .= $js_var . " = " . $js_var . ".replace('[#" . $ph . "#]', " . $value . ");";
		}
		return $output;
	}
}
?>