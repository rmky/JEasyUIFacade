<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\SplitPanel;

/**
 * @method SplitPanel get_widget()
 * @author aka
 *
 */
class euiSplitPanel extends euiPanel {
	private $region = null;
	function generate_html(){
		switch ($this->get_region()){
			case 'north': 
			case 'south': 
				$height = $this->get_height();
				break;
			case 'east': 
			case 'west': 
				$width = $this->get_width();
				break;
			case 'center':
				$height = $this->get_height();
				$width = $this->get_width();
				break;
		}
		
		if ($height && !$this->get_widget()->get_height()->is_percentual()){
			$height = 'calc( ' . $height . ' + 7px)';
		}
		if ($width && !$this->get_widget()->get_width()->is_percentual()){
			$width = 'calc( ' . $width . ' + 7px)';
		}
		
		$style = ($height ? 'height: ' . $height . ';' : '') . ($width ? 'width: ' . $width . ';' : '');
		
		$children_html = $this->build_html_for_children();
		
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