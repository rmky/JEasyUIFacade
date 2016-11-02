<?php
namespace exface\JEasyUiTemplate\Template\Elements;
use exface\Core\Exceptions\TemplateError;
class euiSplitVertical extends euiContainer {
	
	protected function init(){
		parent::init();
		$this->set_element_type('layout');
	}
	
	function generate_html(){	
		$output = '	<div class="easyui-layout" id="' . $this->get_id() . '" data-options="fit:true">
				' . $this->generate_widgets_html() . '
					</div>
				';
		return $output;
	}
	
	function generate_widgets_html(){
		/* @var $widget \exface\Core\Widgets\SplitVertical */
		$widget = $this->get_widget();
		$panels_html = '';
		foreach ($widget->get_panels() as $nr => $panel){
			$elem = $this->get_template()->get_element($panel);
			switch ($nr) {
				case 0: $elem->set_region('north'); break;
				case 1: $elem->set_region('center'); break;
				case 2: $elem->set_region('south'); break;
				default: throw new TemplateError('The template jEasyUI currently only supports splits with a maximum of 3 panels! "' . $widget->get_id() . '" has "' . $widget->count_widgets() . '" panels.');
			}
			$panels_html .= $elem->generate_html();
		}
		
		return $panels_html;
	}
}