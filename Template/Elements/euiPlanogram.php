<?php namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Planogram;
use exface\Core\CommonLogic\Model\RelationPath;

class euiPlanogram extends euiDiagram {
	
	public function generate_html(){
		$output = <<<HTML

<div id="VisualRack" class="easyui-panel" title="{$this->get_widget()->get_caption()}" style="" data-options="fit:true,onResize:function(){ {$this->build_js_function_prefix()}init(); }">
	{$this->get_template()->get_element($this->get_widget()->get_diagram_object_selector_widget())->generate_html()}
    <div id="VisualPlaceholder" style="margin: 10px 3px 0 3px;">

    </div>
</div>
				
		
HTML;
		return $output;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::get_widget()
	 * @return Planogram
	 */
	public function get_widget(){
		return parent::get_widget();
	}
	
	public function generate_js(){
		$widget = $this->get_widget();
		foreach ($widget->get_shapes() as $shape){
			// TODO currently just rendering the last shape
		}
		
		/* @var $relation_to_diagram \exface\Core\CommonLogic\Model\RelationPath */
		$relation_to_diagram = $shape->get_relation_path_to_diagram_object();
		$filter = 'data.fltr01_' . RelationPath::relation_path_add($relation_to_diagram->to_string(), $relation_to_diagram->get_end_object()->get_uid_alias()) . ' = ' . $this->get_template()->get_element($widget->get_diagram_object_selector_widget())->build_js_value_getter() . ';';
		
		$output = <<<JS
		
function {$this->build_js_function_prefix()}init(){
	var background = {'src':'{$widget->get_prefill_data()->get_cell_value($widget->get_background_image_attribute_alias(), 0)}', 'width': 303, 'height': 528};
	getGridInfo(background);
}
		
function getGridInfo(background){
	var data = {};
	data.resource = "{$this->get_page_id()}";
	data.element = "{$shape->get_id()}";
	data.object = "{$shape->get_meta_object()->get_id()}";
	data.action = "{$widget->get_lazy_loading_action()}";
	{$filter}
	
	$.ajax({
		type: "POST",
		url: "{$this->get_ajax_url()}",
		data: data,
		success: function(data){
			setUpDisplay(background, data['rows']);
		},
		dataType: "json"
	});
}
JS;
		return $output . parent::generate_js();
	}
	
	public function build_js_refresh(){
		return $this->build_js_function_prefix() . "init()";
	}
	
	public function generate_headers(){
		$includes = parent::generate_headers();
		$includes[] = '<link rel="stylesheet" media="screen" href="exface/vendor/exface/jEasyUiTemplate/Template/js/planogram/style.css">';
		$includes[] = '<script type="text/javascript" src="exface/vendor/exface/jEasyUiTemplate/Template/js/planogram/gridbuilder.js"></script>';
		$includes[] = '<script type="text/javascript" src="exface/vendor/exface/jEasyUiTemplate/Template/js/planogram/gridinteraction.js"></script>';
		$includes[] = '<script type="text/javascript" src="exface/vendor/exface/jEasyUiTemplate/Template/js/planogram/gridfiller.js"></script>';
		$includes[] = '<script type="text/javascript" src="exface/vendor/exface/jEasyUiTemplate/Template/js/planogram/interact.js"></script>';
		return $includes;
	}
}
?>