<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\ChartAxis;
use exface\Core\Widgets\ChartSeries;
use exface\Core\Widgets\Chart;
use exface\Core\Exceptions\Templates\TemplateUnsupportedWidgetPropertyWarning;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryFlotTrait;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryToolbarsTrait;

/**
 * 
 * @method Chart getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class euiChart extends euiData
{
    
    use JqueryFlotTrait;
    
    use JqueryToolbarsTrait;

    private $on_change_script = '';

    protected function init()
    {
        parent::init();
        $widget = $this->getWidget();
        // Connect to an external data widget if a data link is specified for this chart
        $this->registerLiveReferenceAtLinkedElement();
        
        // Disable global buttons because jEasyUI charts do not have data getters yet
        $widget->getToolbarMain()->setIncludeGlobalActions(false);
        
        // Make the configurator resize together with the chart layout.
        $configurator_element = $this->getTemplate()->getElement($widget->getConfiguratorWidget());
        // FIXME how to make the configurator resize when the chart is resized?
        $this->addOnResizeScript("
            /*if(typeof $('#" . $configurator_element->getId() . "')." . $configurator_element->getElementType() . "() !== 'undefined') {
                setTimeout(function(){
                    $('#" . $configurator_element->getId() . "')." . $configurator_element->getElementType() . "('resize');
                }, 0);     
            }*/
        ");
        
        if ($widget->getHideHeader()){
            $this->addOnResizeScript("
                 var newHeight = $('#{$this->getId()}_wrapper > .panel').height();
                 $('#{$this->getId()}').height($('#{$this->getId()}').parent().height()-newHeight);
            ");
        }
    }

    function generateHtml()
    {
        $output = '';
        $widget = $this->getWidget();
        
        // Create the header if the chart has it's own controls and is not bound to another data widget
        $header_html = '';
        if (! $widget->getDataWidgetLink()) {
            $header_html = $this->buildHtmlTableHeader();
            // Set the height of the canvas-div to auto. Otherwise the chart will be to high in some cases
            // (e.g. in vertical splits, where the chart has filters etc.)
            $canvas_height = 'auto';
        } else {
            // If the chart has no customizir, set the height to 100%. Auto will not work for some reason...
            $canvas_height = '100%';
        }
        
        $chart_panel_options = ", title: '{$this->getCaption()}'";
        
        // Create the panel for the chart
        // overflow: hidden loest ein Problem im JavaFX WebView-Browser, bei dem immer wieder
        // Scrollbars ein- und wieder ausgeblendet wurden. Es trat in Verbindung mit Masonry
        // auf, wenn mehrere Elemente auf einer Seite angezeigt wurden (u.a. ein Chart) und
        // das Layout umgebrochen hat. Da sich die Groesse des Charts sowieso an den Container
        // anpasst sollte overflow: hidden keine weiteren Auswirkungen haben.
        $output = <<<HTML
<div class="exf-grid-item {$this->getMasonryItemClass()}" style="width:{$this->getWidth()};min-width:{$this->getMinWidth()};height:{$this->getHeight()};padding:{$this->getPadding()};box-sizing:border-box;">
    <div class="easyui-panel" style="height: auto;" id="{$this->getId()}_wrapper" data-options="fit: true {$chart_panel_options}, onResize: function(){ {$this->getOnResizeScript()} }">
    	{$header_html}
    	<div id="{$this->getId()}" style="height:{$canvas_height}; min-height: 100px; overflow: hidden;"></div>
    </div>
</div>
HTML;
        
        return $output;
    }

    function generateJs()
    {
        /* @var $widget \exface\Core\Widgets\Chart */
        $widget = $this->getWidget();
        
        $output = '';
        $series_data = '';
        
        if ($this->isPieChart()) {
            $this->getWidget()->setHideAxes(true);
        }
        
        // Add Scripts for the configurator widget first as they may be needed for the others
        $configurator_element = $this->getTemplate()->getElement($widget->getConfiguratorWidget());
        $output .= $configurator_element->generateJs();
        
        // Add scripts for the buttons
        $output .= $this->buildJsButtons();
        
        $output .= <<<JS
                    $('#{$configurator_element->getId()}').find('.grid').on( 'layoutComplete', function( event, items ) {
                        setTimeout(function(){
                            var newHeight = $('#{$this->getId()}_wrapper > .panel').height();
                            console.log(newHeight);
                            $('#{$this->getId()}').height($('#{$this->getId()}').parent().height()-newHeight);
                            console.log($('#{$this->getId()}').height());
                        }, 0);               
                    });
                    
JS;
        
        // Create the function to process fetched data
        $output .= '
			function ' . $this->buildJsFunctionPrefix() . 'plot(ds){
				';
        
        // Transform the input data to a flot dataset
        foreach ($widget->getSeries() as $series) {
            $series_id = $this->sanitizeSeriesId($series->getId());
            $series_column = $series->getDataColumn();
            $x_column = $series->getAxisX()->getDataColumn();
            $y_column = $series->getAxisY()->getDataColumn();
            $output .= '
					var ' . $series_id . ' = [];';
            
            if ($series->getChartType() == ChartSeries::CHART_TYPE_PIE) {
                $series_data = $series_id . '[i] = { label: ds.rows[i]["' . $x_column->getDataColumnName() . '"], data: ds.rows[i]["' . $series_column->getDataColumnName() . '"] }';
            } else {
                // Prepare the code to transform the ajax data to flot data. It will later run in a for loop.
                switch ($series->getChartType()) {
                    case ChartSeries::CHART_TYPE_BARS:
                        $data_key = $series_column->getDataColumnName();
                        $data_value = $y_column->getDataColumnName();
                        break;
                    default:
                        $data_key = $x_column->getDataColumnName();
                        $data_value = $series_column->getDataColumnName();
                }
                $series_data .= '
							' . $series_id . '[i] = [ (ds.rows[i]["' . $data_key . '"]' . ($series->getAxisX()->getAxisType() == 'time' ? '*1000' : '') . '), ds.rows[i]["' . $data_value . '"] ];';
            }
        }
        
        // Prepare other flot options
        $series_config = $this->buildJsSeriesConfig();
        
        foreach ($widget->getAxesX() as $axis) {
            if (! $axis->isHidden()) {
                $axis_x_init .= ', ' . $this->generateAxisOptions($axis);
            }
        }
        foreach ($widget->getAxesY() as $axis) {
            if (! $axis->isHidden()) {
                $axis_y_init .= ', ' . $this->generateAxisOptions($axis);
            }
        }
        
        // Plot flot :)
        $output .= '
					for (var i=0; i < ds.rows.length; i++){
						' . $series_data . '
					}
		
					$.plot("#' . $this->getId() . '",
						' . $this->buildJsSeriesData() . ',
						{
							grid:  { ' . $this->buildJsGridOptions() . ' }
							, crosshair: {mode: "xy"}
							' . ($axis_y_init ? ', yaxes: [ ' . substr($axis_y_init, 2) . ' ]' : '') . '
							' . ($axis_x_init ? ', xaxes: [ ' . substr($axis_x_init, 2) . ' ]' : '') . '
							' . ($series_config ? ', series: { ' . $series_config . ' }' : '') . '
							, legend: { ' . $this->buildJsLegendOptions() . ' }
						}
					);
								
					$(".axisLabels").css("color", "black");
					';
        
        // Call the on_change_script
        $output .= $this->getOnChangeScript();
        
        // End plot() function
        $output .= '}';
        
        // Create the load function to fetch the data via AJAX or from another widget
        $output .= $this->buildJsAjaxLoaderFunction();
        $output .= $this->buildJsTooltipInit();
        
        return $output;
    }

    protected function buildJsGridOptions()
    {
        return 'hoverable: true';
    }

    protected function buildJsLegendOptions()
    {
        $output = '';
        if ($this->isPieChart()) {
            $output .= 'show: false';
        } else {
            $output .= $this->buildJsLegendOptionsAlignment();
        }
        return $output;
    }
    
    protected function buildJsLegendOptionsAlignment()
    {
        $options = '';
        switch (strtoupper($this->getWidget())) {
            case 'LEFT': $options = 'position: "nw"'; break;
            case 'RIGHT': 
            default: $options = 'position: "ne"';
            
        }
        
        return $options;
    }

    protected function isPieChart()
    {
        if ($this->getWidget()->getSeries()[0]->getChartType() == ChartSeries::CHART_TYPE_PIE) {
            return true;
        } else {
            return false;
        }
    }

    protected function buildJsSeriesData()
    {
        $output = '';
        if ($this->isPieChart()) {
            if (count($this->getWidget()->getSeries()) > 1) {
                throw new TemplateUnsupportedWidgetPropertyWarning('The template "' . $this->getTemplate()->getAlias() . '" does not support pie charts with multiple series!');
            }
            
            $output = $this->sanitizeSeriesId($this->getWidget()->getSeries()[0]->getId());
        } else {
            foreach ($this->getWidget()->getSeries() as $series) {
                if ($series->getChartType() == ChartSeries::CHART_TYPE_PIE) {
                    throw new TemplateUnsupportedWidgetPropertyWarning('The template "' . $this->getTemplate()->getAlias() . '" does not support pie charts with multiple series!');
                }
                $series_options = $this->buildJsSeriesOptions($series);
                $output .= ',
								{
									data: ' . $this->sanitizeSeriesId($series->getId()) . ($series->getChartType() == ChartSeries::CHART_TYPE_BARS ? '.reverse()' : '') . '
									, label: "' . $series->getCaption() . '"
									, yaxis:' . $series->getAxisY()->getNumber() . '
									, xaxis:' . $series->getAxisX()->getNumber() . '
									' . ($series_options ? ', ' . $series_options : '') . '
								}';
            }
            $output = '[' . substr($output, 2) . ']';
        }
        return $output;
    }

    /**
     * Returns the definition of the function elementId_load(urlParams) which is used to fethc the data via AJAX
     * if the chart is not bound to another data widget (in that case, the data should be provided by that widget).
     *
     * @return string
     */
    protected function buildJsAjaxLoaderFunction()
    {
        $widget = $this->getWidget();
        $output = '';
        if (! $widget->getDataWidgetLink()) {
            
            $url_params = '
                            resource: "' . $widget->getPage()->getAliasWithNamespace() . '"
                            , element: "' . $widget->getData()->getId(). '"
                            , object: "' . $widget->getMetaObject()->getId(). '"
                            , action: "' . $widget->getLazyLoadingAction(). '"
            ';
            
            // send sort information
            if (count($widget->getData()->getSorters()) > 0) {
                foreach ($widget->getData()->getSorters() as $sorter) {
                    $sort .= ',' . urlencode($sorter->getProperty('attribute_alias'));
                    $order .= ',' . urldecode($sorter->getProperty('direction'));
                }
                $url_params .= '
                            , sort: "' . substr($sort, 1) . '"
                            , order: "' . substr($order, 1) . '"';
            }
            
            // send pagination/limit information. Charts currently do not support real pagination, but just a TOP-X display.
            if ($widget->getData()->getPaginate()) {
                $url_params .= '
                            , page: 1
                            , rows: ' . (! is_null($widget->getData()->getPaginatePageSize()) ? $widget->getData()->getPaginatePageSize() : $this->getTemplate()->getConfig()->getOption('WIDGET.CHART.PAGE_SIZE'));
            }
            
            // Loader function
            $output .= '
				function ' . $this->buildJsFunctionPrefix() . 'load(){
					' . $this->buildJsBusyIconShow() . '
					$.ajax({
						url: "' . $this->getAjaxUrl() . '",
                        method: "POST",
                        data: {
                            ' . $url_params . '
                            , data: ' . $this->getTemplate()->getElement($widget->getConfiguratorWidget())->buildJsDataGetter() . '
                            
                        },
						success: function(data){
							' . $this->buildJsFunctionPrefix() . 'plot($.parseJSON(data));
							' . $this->buildJsBusyIconHide() . '
						},
						error: function(jqXHR, textStatus, errorThrown){
							' . $this->buildJsShowError('jqXHR.responseText', 'jqXHR.status + " " + jqXHR.statusText') . '
							' . $this->buildJsBusyIconHide() . '
						}
					});
				}';
            
            // Call the data loader to populate the Chart initially
            $output .= $this->buildJsRefresh();
        }
        
        return $output;
    }
    
    /**
     * 
     */
    public function buildJsRefresh()
    {
        return $this->buildJsFunctionPrefix() . 'load();';
    }

    protected function buildJsTooltipInit()
    {
        // Create a tooltip generator function
        // TODO didn't work because I don't know, how to get the axes infomration from an instantiated plot
        $output = '
		 $(\'<div class="tooltip-inner" id="' . $this->getId() . '_tooltip"></div>\').css({
		      position: "absolute",
		      display: "none",
		      opacity: 0.8
		    }).appendTo("body");
		    $("#' . $this->getId() . '").bind("plothover", function (event, pos, item) {
		      if (item) {
                try {
    		        var x = new Date(item.datapoint[0]),
    		            y = isNaN(item.datapoint[1]) ? item.datapoint[1] : item.datapoint[1].toFixed(2);
    		
    		        $("#' . $this->getId() . '_tooltip").html(item.series.xaxis.options.axisLabel + ": " + x.toLocaleDateString() + "<br/>" + item.series.label + ": " + y)
    		            .css({top: item.pageY + 5, left: item.pageX + 5})
    		            .fadeIn(200);
                } catch (e) {
                    // ignore errors
                }
		      } else {
		        $("#' . $this->getId() . '_tooltip").hide();
		      }
		
		    });
				';
        return $output;
    }

    public function sanitizeSeriesId($string)
    {
        return str_replace(array(
            '.',
            '(',
            ')',
            '=',
            ',',
            ' '
        ), '_', $string);
    }

    public function buildJsSeriesOptions(ChartSeries $series)
    {
        $options = '';
        $color = $series->getDataColumn()->getColor();
        switch ($series->getChartType()) {
            case ChartSeries::CHART_TYPE_LINE:
            case ChartSeries::CHART_TYPE_AREA:
                $options = 'lines: 
								{
									show: true
									' . ($series->getChartType() == ChartSeries::CHART_TYPE_AREA ? ', fill: true' : '') . '
                                }
                            ' . ($color ? ', color: "' . $color . '"' : '') . '';
                break;
            case ChartSeries::CHART_TYPE_BARS:
            case ChartSeries::CHART_TYPE_COLUMNS:
                $options = 'bars: 
								{
									show: true 
                                    , lineWidth: 0
									, align: "center"
                                    ';
                if (! $series->getChart()->getStackSeries() && count($series->getChart()->getSeriesByChartType($series->getChartType())) > 1) {
                    $options .= '
                                    , barWidth: 0.2
                                    , order: ' . $series->getSeriesNumber();
                } else {
                    $options .= '
                                    , barWidth: 0.8';
                }
                
                if ($series->getAxisX()->getAxisType() == ChartAxis::AXIS_TYPE_TIME || $series->getAxisY()->getAxisType() == ChartAxis::AXIS_TYPE_TIME) {
                    $options .= '
									, barWidth: 24*60*60*1000';
                }
                
                if ($series->getChartType() == ChartSeries::CHART_TYPE_BARS) {
                    $options .= '
									, horizontal: true';
                }
                
                $options .= '
								}
                            ' . ($color ? ', color: "' . $color . '"' : '') . '';
                break;
            case ChartSeries::CHART_TYPE_PIE:
                $options = 'pie: {show: true}';
                break;
        }
        return $options;
    }

    private function generateAxisOptions(ChartAxis $axis)
    {
        /* @var $widget \exface\Core\Widgets\Chart */
        $widget = $this->getWidget();
        $output = '{
								axisLabel: "' . $axis->getCaption() . '"
								, position: "' . strtolower($axis->getPosition()) . '"' . ($axis->getPosition() == ChartAxis::POSITION_RIGHT || $axis->getPosition() == ChartAxis::POSITION_TOP ? ', alignTicksWithAxis: 1' : '') . (is_numeric($axis->getMinValue()) ? ', min: ' . $axis->getMinValue() : '') . (is_numeric($axis->getMaxValue()) ? ', max: ' . $axis->getMaxValue() : '');
        
        switch ($axis->getAxisType()) {
            case ChartAxis::AXIS_TYPE_TEXT:
                $output .= '
								, mode: "categories"';
                break;
            case ChartAxis::AXIS_TYPE_TIME:
                $output .= '
								, mode: "time"';
                break;
            default:
        }
        
        $output .= '
					}';
        return $output;
    }

    public function generateHeaders()
    {
        $includes = parent::generateHeaders();
        // flot
        $includes[] = '<script type="text/javascript" src="exface/vendor/npm-asset/flot-charts/jquery.flot.js"></script>';
        $includes[] = '<script type="text/javascript" src="exface/vendor/npm-asset/flot-charts/jquery.flot.resize.js"></script>';
        $includes[] = '<script type="text/javascript" src="exface/vendor/npm-asset/flot-charts/jquery.flot.categories.js"></script>';
        $includes[] = '<script type="text/javascript" src="exface/vendor/npm-asset/flot-charts/jquery.flot.time.js"></script>';
        $includes[] = '<script type="text/javascript" src="exface/vendor/npm-asset/flot-charts/jquery.flot.crosshair.js"></script>';
        $includes[] = '<script type="text/javascript" src="exface/vendor/exface/JEasyUiTemplate/Template/js/flot/plugins/axislabels/jquery.flot.axislabels.js"></script>';
        $includes[] = '<script type="text/javascript" src="exface/vendor/exface/JEasyUiTemplate/Template/js/flot/plugins/jquery.flot.orderBars.js"></script>';
        
        if ($this->getWidget()->getStackSeries()) {
            $includes[] = '<script type="text/javascript" src="exface/vendor/npm-asset/flot-charts/jquery.flot.stack.js"></script>';
        }
        
        if ($this->isPieChart()) {
            $includes[] = '<script type="text/javascript" src="exface/vendor/npm-asset/flot-charts/jquery.flot.pie.js"></script>';
        }
        
        // masonry for proper filter alignment
        $includes[] = '<script type="text/javascript" src="exface/vendor/bower-asset/masonry/dist/masonry.pkgd.min.js"></script>';
        return $includes;
    }

    protected function buildJsSeriesConfig()
    {
        $output = '';
        $config_array = array();
        foreach ($this->getWidget()->getSeries() as $series) {
            switch ($series->getChartType()) {
                case ChartSeries::CHART_TYPE_PIE:
                    $config_array[$series->getChartType()]['show'] = 'show: true';
                    $config_array[$series->getChartType()]['radius'] = 'radius: 1';
                    $config_array[$series->getChartType()]['label'] = 'label: {
							show: true, 
							radius: 0.8, 
							formatter: function (label, series) {
								return "<div style=\'font-size:8pt; text-align:center; padding:2px; color:white;\'>" + label + "<br/>" + Math.round(series.percent) + "%</div>";
							}, 
							background: {opacity: 0.8}}';
                    break;
                case ChartSeries::CHART_TYPE_COLUMNS:
                case ChartSeries::CHART_TYPE_BARS:
                    
                    break;
                default:
                    break;
            }
        }
        
        if ($this->getWidget()->getStackSeries()) {
            $config_array['stack'] = 'true';
        }
        
        foreach ($config_array as $chart_type => $options) {
            $output .= $chart_type . ': ' . (is_array($options) ? '{' . implode(', ', $options) . '}' : $options) . ', ';
        }
        
        $output = $output ? substr($output, 0, - 2) : $output;
        return $output;
    }

    public function addOnChangeScript($string)
    {
        $this->on_change_script .= $string . ';';
        return $this;
    }

    public function getOnChangeScript()
    {
        return $this->on_change_script;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\euiAbstractElement::getHeight()
     */
    function getHeight()
    {
        // Die Hoehe des Charts passt sich nicht automatisch dem Inhalt an. Wenn er also
        // nicht den gesamten Container ausfuellt, kollabiert er vollstaendig. Deshalb
        // wird hier die Hoehe des Charts gesetzt, wenn sie nicht definiert ist, und
        // er nicht alleine im Container ist.
        $widget = $this->getWidget();
        
        if ($widget->getHeight()->isUndefined()) {
            if (($containerWidget = $widget->getParentByType('exface\\Core\\Interfaces\\Widgets\\iContainOtherWidgets')) && ($containerWidget->countWidgetsVisible() > 1)) {
                $widget->setHeight($this->getTemplate()->getConfig()->getOption('WIDGET.CHART.HEIGHT_DEFAULT'));
            }
        }
        return parent::getHeight();
    }
    
    public function buildJsBusyIconShow()
    {
        return "$('#{$this->getId()}_wrapper').prepend('<div class=\"panel-loading\" style=\"height: 15px;\"></div>');";
    }
    
    public function buildJsBusyIconHide()
    {
        return "$('#{$this->getId()}_wrapper .panel-loading').remove();";
    }
}
?>