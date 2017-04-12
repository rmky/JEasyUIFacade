<?php namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputGroup extends euiPanel {
	
	public function generate_html() {
		$children_html = $this->build_html_for_children();
		
		// Wrap children widgets with a grid for masonry layouting - but only if there is something to be layed out
		if ($this->get_widget()->count_widgets() > 1){
			$children_html = '<div class="grid">' . $children_html . '</div>';
		}
		
		$output = '
				<fieldset class="exface_inputgroup 
						id="'.$this->get_id().'" 
						data-options="'.$this->build_js_data_options().'">
					<legend>'.$this->get_widget()->get_caption().'</legend>
					'.$children_html.'
				</fieldset>';
		return $output;
	}
	
}
?>