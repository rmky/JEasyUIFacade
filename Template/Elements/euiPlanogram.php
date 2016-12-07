<?php namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Planogram;
use exface\Core\CommonLogic\Model\RelationPath;

class euiPlanogram extends euiDiagram {
	
	public function generate_html(){
		$button_html = "";
		foreach ($this->get_widget()->get_shapes() as $shape){
			foreach ($shape->get_data()->get_buttons() as $button){
				$button_html .= $this->get_template()->get_element($button)->generate_html() . "\n";
				$menu_html .= $this->get_template()->get_element($button)->build_html_button();
			}
			// Create a context menu if any items were found
			if (count($shape->get_data()->get_buttons()) > 1 && $menu_html){
				$menu_html = '<div id="' . $this->get_id() . '_smenu" class="easyui-menu">' . $menu_html . '</div>';
			} else {
				$menu_html = '';
			}
		}
		$output = <<<HTML

<div id="VisualRack" class="easyui-panel" title="{$this->get_widget()->get_caption()}" style="" data-options="fit:true,tools:'#{$this->get_id()}_tools',onResize:function(){ {$this->build_js_function_prefix()}init(); }">
	{$this->get_template()->get_element($this->get_widget()->get_diagram_object_selector_widget())->generate_html()}
    <div id="VisualPlaceholder" style="margin: 10px 3px 0 3px;">

    </div>
	<div id="{$this->get_id()}_tools">
		<a href="http://nbdr223.salt-solutions.de/exface/319.html" class="icon-link" title="Preview" target="_blank"></a>
		<a href="javascript:void(0)" class="icon-reload" onclick="javascript:{$this->build_js_function_prefix()}init()" title="{$this->get_template()->get_app()->get_translator()->translate('REFRESH')}"></a>
	</div>
	<div style="display:none">
		{$menu_html}
		{$button_html}
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
		$actions_js = '';
		foreach ($widget->get_shapes() as $shape){
			// TODO currently just rendering the last shape
			
			/* @var $button \exface\Core\Widgets\Button */
			/* @var $button_element \exface\JEasyUiTemplate\Template\Elements\euiButton */
			foreach ($shape->get_data()->get_buttons() as $button){
				$button_element = $this->get_template()->get_element($button);
				$actions_js .= $button_element->generate_js() . "\n";
				$shape_click_js = $button_element->build_js_click_function_name() . '();';
			}
			
			if (count($shape->get_data()->get_buttons()) > 1){
				$shape_click_js = '$("#' . $this->get_id() . '_smenu").menu("show", {
	                    left: e.pageX,
	                    top: e.pageY
	                });';
			}
		}
		
		/* @var $relation_to_diagram \exface\Core\CommonLogic\Model\RelationPath */
		$relation_to_diagram = $shape->get_relation_path_to_diagram_object();
		$filter = 'data.fltr01_' . RelationPath::relation_path_add($relation_to_diagram->to_string(), $relation_to_diagram->get_end_object()->get_uid_alias()) . ' = ' . $this->get_template()->get_element($widget->get_diagram_object_selector_widget())->build_js_value_getter() . ';';
		$bg_image = $widget->get_prefill_data()->get_cell_value($widget->get_background_image_attribute_alias(), 0);
		$bg_image = $bg_image ? $bg_image : '{}';
		$output = <<<JS
		
function {$this->build_js_function_prefix()}init(){
	var background = {$bg_image};
	getGridInfo(background);
}

$(document).ready(function(){
	$("body").on('click', '#VisualPlaceholder svg polygon', function(){
        alert("My name is "+$(this).data("oid"));
    });
    
    $("body").on('click', '#VisualPlaceholder svg text', function(e){
   		{$this->get_id()}_selected = $(this).parent();
        {$shape_click_js}
    });
    
    interact('tr.datagrid-row').draggables({max: 2});
});
		
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
		
function getArticles(){
	var data = {};
	var result = [];
	data.resource = "{$this->get_page_id()}";
	data.element = "{$shape->get_data()->get_id()}";
	data.object = "{$shape->get_data()->get_meta_object()->get_id()}";
	data.action = "{$widget->get_lazy_loading_action()}";
	
	$.ajax({
		type: "POST",
		url: "{$this->get_ajax_url()}",
		async: false,
		data: data,
		success: function(data){
			result = data;
		},
		dataType: "json"
	});
				
	return result;
}
		
{$actions_js}
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