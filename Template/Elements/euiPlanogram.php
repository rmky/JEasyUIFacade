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

<div id="{$this->get_id()}_panel" class="easyui-panel" title="{$this->get_widget()->get_caption()}" style="" data-options="fit:true,tools:'#{$this->get_id()}_tools',onResize:function(){ if ($('#{$this->get_id()} svg').length > 0) { $('#{$this->get_id()}').planogram({width: $(this).width(), height: $(this).height()})}}">
	{$this->get_template()->get_element($this->get_widget()->get_diagram_object_selector_widget())->generate_html()}
    <div id="{$this->get_id()}" style="margin: 10px 3px 0 3px; text-align: center;">

    </div>
	<div id="{$this->get_id()}_tools">
		<a href="http://nbdr223.salt-solutions.de/exface/319.html" class="icon-link" title="Preview" target="_blank"></a>
		<a href="#" onclick="{$this->build_js_refresh()};" class="icon-reload" title="{$this->get_template()->get_app()->get_translator()->translate('REFRESH')}"></a>
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
			
			// Shape data to display within each shape
			$data_display_rows = array();
			foreach ($shape->get_data()->get_columns() as $column){
				if ($column->is_hidden()) continue;
				$data_display_rows[] = "[{type:'param', val:'" . $column->get_data_column_name() . "'}]";
			}
			$data_display = implode(',', $data_display_rows);
			
			// Shape data buttons for click-menus
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
		$relation_from_data_to_diagram = $shape->get_data()->get_meta_object()->find_relation_path($widget->get_meta_object())->to_string();
		$filter_shape_options = 'data.fltr01_' . RelationPath::relation_path_add($relation_to_diagram->to_string(), $relation_to_diagram->get_end_object()->get_uid_alias()) . ' = ' . $this->get_template()->get_element($widget->get_diagram_object_selector_widget())->build_js_value_getter() . ';';
		$filter_shape_data = 'data.fltr01_' . $relation_from_data_to_diagram . ' = ' . $this->get_template()->get_element($widget->get_diagram_object_selector_widget())->build_js_value_getter() . ';';
		
		$bg_image = $widget->get_prefill_data()->get_cell_value($widget->get_background_image_attribute_alias(), 0);
		if ($bg_image){
			$bg_image_size = getimagesize($widget->get_workbench()->filemanager()->get_path_to_base_folder() . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $bg_image);
		} else {
			$bg_image_size = array(0,0);
		}
		if ($bg_image_size[1] > 800){
			$width = "100%";
			$height = "auto";
		} else {
			$width = "auto";
			$height = "90%";
		}
		
		if ($widget->get_add_row_link_button_id()) {
			$add_row_function = $this->get_template()->get_element_by_widget_id($widget->get_add_row_link_button_id(), $this->get_page_id())->build_js_click_function();
		}
		
		$output = <<<JS

$(document).ready(function(){
	var {$this->get_id()}planogram = $("#{$this->get_id()}").planogram({
    	background: '{$bg_image}',
    	backgroundStretch: 'fit',
		shapeLoader: {$this->build_js_function_prefix()}shapeLoader,
		dataLoader: {$this->build_js_function_prefix()}dataLoader,
		boxWidth: {$bg_image_size[0]},
		boxHeight: {$bg_image_size[1]},
		width: "{$width}",
		height: "{$height}",
		parentElement: $("#{$this->get_id()}").parentsUntil(".panel", ".panel-body"),
		onLoad: function(){ {$this->build_js_busy_icon_hide()} },
		onShapeClick: function(data){
			{$this->get_id()}_selected = $(this).parent();
        	{$shape_click_js}
		},
		onDrop: function(plugin, dragItem, dropArea){
            var draggableItemShelf = $(dragItem).attr("data-shelf-oid");
            var draggableOID = $(dragItem).attr("data-oid");
            var enteredItemShelf = $(dropArea).attr("data-oid");

            if (draggableItemShelf == enteredItemShelf) {
                resetElement(dragItem);
                console.log("Dropped in same shelf - nothing is accomplished");
                return;
            }
            else {
                console.log("Element with ID " + draggableOID + " from Shelf " + draggableItemShelf + " was dropped in Shelf " + enteredItemShelf);
                {$add_row_function}
                return;
            }
        },
		shapeOptionsDefaults: {
            style: {'shape-fill': 'rgba(184,229,229,0.6)',
                    'shape-stroke-width': 1,
                    'shape-stroke':'rgb(121,205,205)',
                    'text-fill':'rgb(0,0,0)',
                    'text-stroke-width': 0,
                    'text-font-family': 'Arial',
                    'text-font-size'   : 12,
                    },
            titleBoxOffset: [4,4,"bottomright"],             //negative offset for area name [x,y,position]
            id: '{$widget->get_shapes()[0]->get_meta_object()->get_uid_alias()}',
            label: '{$widget->get_shapes()[0]->get_shape_caption_attribute_alias()}',
            options: '{$widget->get_shapes()[0]->get_shape_options_attribute_alias()}'
        },
		dataTextField: [
           {$data_display}
        ]
	});

	$("body").on('click', '#VisualPlaceholder svg polygon', function(){
        alert("My name is "+$(this).data("oid"));
    });
    
    $("body").on('click', '#{$this->get_id()} svg text', function(e){
   		{$this->get_id()}_selected = $(this).parent();
        {$shape_click_js}
    });
    
    interact('tr.datagrid-row').draggables({max: 2});
});
		
function {$this->build_js_function_prefix()}shapeLoader(){
	{$this->build_js_busy_icon_show()}
	var data = {};
	var diagram = this;
	data.resource = "{$this->get_page_id()}";
	data.element = "{$shape->get_id()}";
	data.object = "{$shape->get_meta_object()->get_id()}";
	data.action = "{$widget->get_lazy_loading_action()}";
	{$filter_shape_options}
	
	$.ajax({
		type: "POST",
		url: "{$this->get_ajax_url()}",
		data: data,
		success: function(data){
			diagram.setAreaData(data['rows']);
		},
		dataType: "json"
	});
}
		
function {$this->build_js_function_prefix()}dataLoader(){
	{$this->build_js_busy_icon_show()}
	var data = {};
	var result = [];
	var diagram = this;
	data.resource = "{$this->get_page_id()}";
	data.element = "{$shape->get_data()->get_id()}";
	data.object = "{$shape->get_data()->get_meta_object()->get_id()}";
	data.action = "{$shape->get_data()->get_lazy_loading_action()}";
	{$filter_shape_data}
	
	$.ajax({
		type: "POST",
		url: "{$this->get_ajax_url()}",
		data: data,
		success: function(data){
			diagram.setElementData(data['rows']);
		},
		dataType: "json"
	});
}
		
{$actions_js}
JS;
		return $output . parent::generate_js();
	}
	
	public function build_js_refresh(){
		return "$('#" . $this->get_id() . "').planogram('refreshData');";
	}
	
	public function generate_headers(){
		$includes = parent::generate_headers();
		//$includes[] = '<link rel="stylesheet" media="screen" href="exface/vendor/exface/jEasyUiTemplate/Template/js/planogram/style.css">';
		$includes[] = '<script type="text/javascript" src="exface/vendor/exface/jEasyUiTemplate/Template/js/planogram/planogram.plugin.js"></script>';
		$includes[] = '<script type="text/javascript" src="exface/vendor/exface/jEasyUiTemplate/Template/js/planogram/interact.js"></script>';
		return $includes;
	}
	
	public function build_js_busy_icon_show(){
		return 'if($("#' . $this->get_id() . ' .datagrid-mask").length == 0) $("#' . $this->get_id() . '").append(\'<div class="datagrid-mask" style="display:block"></div><div class="datagrid-mask-msg" style="display: block; left: 50%; height: 40px; margin-left: -90px; line-height: 40px;">Processing, please wait ...</div>\');';
	}
	
	public function build_js_busy_icon_hide(){
		return '$("#' . $this->get_id() . ' .datagrid-mask").remove();$("#' . $this->get_id() . ' .datagrid-mask-msg").remove();';
	}
}
?>