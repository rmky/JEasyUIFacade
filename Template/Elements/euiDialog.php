<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiDialog extends euiForm {
	private $buttons_div_id = '';
	
	protected function init(){
		parent::init();
		$this->buttons_div_id = $this->get_id() . '-buttons';
		$this->set_element_type('dialog');
	}
	
	function generate_html(){
		$contents = ($this->get_widget()->get_lazy_loading() ? '' : $this->build_html_for_widgets());
		
		if (!$this->get_widget()->get_hide_help_button()){
			$window_tools = '<a href="javascript:' . $this->get_template()->get_element($this->get_widget()->get_help_button())->build_js_click_function_name() . '()" class="icon-help"></a>';
		}
		
		$dialog_title = str_replace('"', '\"', $this->get_widget()->get_caption());
		
		$output = <<<HTML
	<div class="easyui-dialog" id="{$this->get_id()}" data-options="{$this->build_js_data_options()}" title="{$dialog_title}" style="width: {$this->get_width()}; height: {$this->get_height()};">
		{$contents}		
	</div>
	<div id="{$this->buttons_div_id}">
		{$this->build_html_buttons()}
	</div>
	<div id="{$this->get_id()}_window_tools">
		{$window_tools}
	</div>
HTML;
		return $output;
	}
	
	function generate_js(){
		$output = '';
		if (!$this->get_widget()->get_lazy_loading()){
			$output .= $this->build_js_for_widgets();
		}
		$output .= $this->build_js_buttons();
		
		// Add the help button in the bottom toolbar
		if (!$this->get_widget()->get_hide_help_button()){
			$output .= $this->get_template()->generate_js($this->get_widget()->get_help_button());
		}
		
		return $output;
	}
	
	/**
	 * Generates the contents of the data-options attribute (e.g. iconCls, collapsible, etc.)
	 * @return string
	 */
	function build_js_data_options(){
		$this->add_on_load_script("$('#" . $this->get_id() . " .exf_input input').first().next().find('input').focus();");
		/* @var $widget \exface\Core\Widgets\Dialog */
		$widget = $this->get_widget();
		// TODO make the Dialog responsive as in http://www.jeasyui.com/demo/main/index.php?plugin=Dialog&theme=default&dir=ltr&pitem=
		$output = parent::build_js_data_options() .
				($widget->get_maximizable() ? ', maximizable: true, maximized: ' . ($widget->get_maximized() ? 'true' : 'false') : '') .
				", cache: false" .
				", closed: false" .
				", buttons: '#{$this->buttons_div_id}'" .
				", tools: '#{$this->get_id()}_window_tools'" .
				", modal: true"
				;		
		return $output;
	}
	
	function get_width(){
		if ($this->get_widget()->get_width()->is_undefined()){
			$this->get_widget()->set_width((2 * $this->get_width_relative_unit() + 35) . 'px');
		}
		return parent::get_width();
	}
	
	function get_height(){
		if ($this->get_widget()->get_height()->is_undefined()){
			$this->get_widget()->set_height('80%');
		}
		return parent::get_height();
	}  
}
?>