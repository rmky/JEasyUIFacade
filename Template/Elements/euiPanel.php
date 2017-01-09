<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Panel;

/**
 * The Panel widget is mapped to a panel in jEasyUI
 * 
 * @author Andrej Kabachnik
 *
 * @method Panel get_widget()
 */
class euiPanel extends euiContainer {
	
	private $on_load_script = '';
	private $on_resize_script = '';
	
	protected function init(){
		parent::init();
		$this->set_element_type('panel');
	}
	
	public function generate_html(){
		$children_html = $this->build_html_for_children();
		
		// Wrap children widgets with a grid for masonry layouting - but only if there is something to be layed out
		if ($this->get_widget()->count_widgets() > 1){
			$children_html = '<div class="grid">' . $children_html . '</div>';
		}
		
		// A standalone panel will always fill out the parent container (fit: true), but
		// other widgets based on a panel may not do so. Thus, the fit data-option is added
		// here, in the generate_html() method, which is verly likely to be overridden in 
		// extending classes!
		$output = '
				<div class="easyui-' . $this->get_element_type() . '" 
					id="' . $this->get_id() . '"
					data-options="' . $this->generate_data_options() . ', fit: true" 
					title="' . $this->get_widget()->get_caption() . '">
					' . $children_html . '
				</div>';
		return $output;
	}
	
	/**
	 * Generates the contents of the data-options attribute (e.g. iconCls, collapsible, etc.)
	 * @return string
	 */
	function generate_data_options(){
		/* @var $widget \exface\Core\Widgets\Panel */
		$widget = $this->get_widget();
		if ($widget->get_column_number() != 1){
			$this->add_on_load_script($this->build_js_layouter());
			$this->add_on_resize_script($this->build_js_layouter());
		}
		
		$output = "collapsible: " . ($widget->get_collapsible() ? 'true' : 'false') .
				($widget->get_icon_name() ? ", iconCls:'" . $this->build_css_icon_class($widget->get_icon_name()) . "'" : '') .
				($this->get_on_load_script() ? ", onLoad: function(){" . $this->get_on_load_script() . "}" : '') .
				($this->get_on_resize_script() ? ", onResize: function(){" . $this->get_on_resize_script() . "}" : '')
				;		
		return $output;
	}
	
	function generate_buttons_html(){
		$output = '';
		foreach ($this->get_widget()->get_buttons() as $btn){
			$output .= $this->get_template()->generate_html($btn);
		}
	
		return $output;
	}
	
	function generate_buttons_js(){
		$output = '';
		foreach ($this->get_widget()->get_buttons() as $btn){
			$output .= $this->get_template()->generate_js($btn);
		}
	
		return $output;
	}
	
	public function generate_headers(){
		$includes = parent::generate_headers();
		if ($this->get_widget()->get_column_number() != 1){
			$includes[] = '<script type="text/javascript" src="exface/vendor/bower-asset/masonry/dist/masonry.pkgd.min.js"></script>';
		}
		return $includes;
	}
	
	public function get_on_load_script() {
		return $this->on_load_script;
	}
	
	public function add_on_load_script($value) {
		$this->on_load_script .= $value;
		return $this;
	}  
	
	public function get_on_resize_script() {
		return $this->on_resize_script;
	}
	
	public function add_on_resize_script($value) {
		$this->on_resize_script .= $value;
		return $this;
	}  
	
	public function build_js_layouter(){
		$script .= <<<JS
	if (!$('#{$this->get_id()} .grid').data('masonry')){
		if ($('#{$this->get_id()} .grid').find('.fitem').length > 0){
			$('#{$this->get_id()} .grid').masonry({itemSelector: '.fitem', columnWidth: {$this->get_width_relative_unit()}});
		}
	} else {
		$('#{$this->get_id()} .grid').masonry('reloadItems');
		$('#{$this->get_id()} .grid').masonry();
	}
JS;
		return $script;
	}
}
?>