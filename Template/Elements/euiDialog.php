<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiDialog extends euiPanel {
	private $buttons_div_id = '';
	
	protected function init(){
		parent::init();
		$this->buttons_div_id = $this->get_id() . '-buttons';
		$this->set_element_type('dialog');
	}
	
	function generate_html(){
		$contents = ($this->get_widget()->get_lazy_loading() ? '' : $this->build_html_for_widgets());
		$output = <<<HTML
	<div class="easyui-dialog" id="{$this->get_id()}" data-options="{$this->generate_data_options()}" title="{$this->get_widget()->get_caption()}" style="width: {$this->get_width()}; height: {$this->get_height()};">
		{$contents}		
	</div>
	{$this->build_html_buttons()}
HTML;
		return $output;
	}
	
	function build_html_buttons() {
		$button_align_groups = [];
		$output = '';
		
		// The buttons are first separated into align groups.
		foreach ($this->get_widget()->get_buttons() as $btn){
			$button_align = $btn->get_align() ? $btn->get_align() : EXF_ALIGN_RIGHT;
			$button_align_groups[$button_align][] = $this->get_template()->generate_html($btn);
		}
		
		// Each align group is wrapped into one div.
		foreach ($button_align_groups as $align => $group) {
			$output .= "<div style=\"float:".$align.";height:".$this->get_template()->get_element($btn)->get_height().";\">\n";
			foreach ($group as $button_html) {
				$output .= "	".$button_html."\n";
			}
			$output .= "</div>\n";
		}
		
		//Wrapper div for everything, the height has to be set explicitely.
		$output = "<div id=\"".$this->buttons_div_id."\" style=\"height:".$this->get_template()->get_element($btn)->get_height().";\">\n"
					.$output
				."</div>\n";
		
		return $output;
	}
	
	function generate_js(){
		$output = '';
		if (!$this->get_widget()->get_lazy_loading()){
			$output .= $this->build_js_for_widgets();
		}
		$output .= $this->build_js_buttons();
		return $output;
	}
	
	/**
	 * Generates the contents of the data-options attribute (e.g. iconCls, collapsible, etc.)
	 * @return string
	 */
	function generate_data_options(){
		$this->add_on_load_script("$('#" . $this->get_id() . " .exf_input input').first().next().find('input').focus();");
		/* @var $widget \exface\Core\Widgets\Dialog */
		$widget = $this->get_widget();
		// TODO make the Dialog responsive as in http://www.jeasyui.com/demo/main/index.php?plugin=Dialog&theme=default&dir=ltr&pitem=
		$output = parent::generate_data_options() .
				($widget->get_maximizable() ? ', maximizable: true, maximized: ' . ($widget->get_maximized() ? 'true' : 'false') : '') .
				", cache: false" .
				", closed: true" .
				", buttons: '#{$this->buttons_div_id}'" .
				", modal: true" .
				// TODO this href must return the contents of the dialog. Since draw only takes exactly one widget it will probably be
				// neccessary to wrap the dialog contents in a container widget if lazy_loading is allowed. 
				// TODO Make async dialogs prefill-compatible: somehow we need to pass the instance-UID of the dialogs meta_object back to the server... 
				", href: '" . $this->get_ajax_url() . "&f=draw&resource=" . $this->get_page_id() . "&element=" . $this->get_id() . "'" .
				", method: 'post'" . 
				", onLoadError: function(response){ $.parser.parse($(this).panel('clear').panel('body').append(response.responseText)); }"
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