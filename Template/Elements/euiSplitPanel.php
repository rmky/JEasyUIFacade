<?php
namespace exface\JEasyUiTemplate\Template\Elements;
class euiSplitPanel extends euiPanel {
	private $region = null;
	function generate_html(){
		switch ($this->get_region()){
			case 'north': case 'south': $style = 'height: calc(' . $this->get_height() . ' + 7px);'; break;
			case 'east': case 'west': $style = 'width: calc(' . $this->get_width() . ' + 7px);'; break;
			case 'center': $style = 'width: calc(' . $this->get_width() . ' + 7px);height: calc(' . $this->get_height() . ' + 7px);'; break;
		}
		
		$children_html = $this->children_generate_html();
		
		// Wrap children widgets with a grid for masonry layouting - but only if there is something to be layed out
		if ($this->get_widget()->count_widgets() > 1){
			$children_html = '<div class="grid">' . $children_html . '</div>';
		}
		
		$output = '
				<div id="' . $this->get_id() . '" data-options="' . $this->generate_data_options() . '"' . ($style ? ' style="' . $style . '"' : '') . '>
					' . $children_html . '
				</div>
				';
		return $output;
	}
	
	public function generate_data_options(){
		/* @var $widget \exface\Core\Widgets\LayoutPanel */
		$widget = $this->get_widget();
		$output = parent::generate_data_options();
		
		$output .= ($output ? ',' : '') . 'region:\'' . $this->get_region() . '\'
					,title:\'' . $widget->get_caption() . '\''
					. ($this->get_region() !== 'center' ? ',split:' . ($widget->get_resizable() ? 'true' : 'false') : '');
		
		return $output;
	}
	
	public function get_region() {
		return $this->region;
	}
	
	public function set_region($value) {
		$this->region = $value;
		return $this;
	}  
}
?>