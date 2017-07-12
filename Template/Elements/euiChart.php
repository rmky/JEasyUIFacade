<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\ChartAxis;
use exface\Core\Widgets\ChartSeries;
use exface\Core\Widgets\Chart;
use exface\Core\Exceptions\Templates\TemplateUnsupportedWidgetPropertyWarning;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryFlotTrait;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryLayoutInterface;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryLayoutTrait;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryToolbarsTrait;

/**
 * 
 * @method Chart getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class euiChart extends euiAbstractElement implements JqueryLayoutInterface
{
    
    use JqueryFlotTrait;
    use JqueryLayoutTrait;
    use JqueryToolbarsTrait;

    private $on_change_script = '';

    protected function init()
    {
        parent::init();
        // Connect to an external data widget if a data link is specified for this chart
        $this->registerLiveReferenceAtLinkedElement();
    }

    protected function getToolbarId()
    {
        return $this->getId() . '_toolbar';
    }

    function generateHtml()
    {
        $output = '';
        $toolbar = '';
        $widget = $this->getWidget();
        
        // Create the toolbar if the chart has it's own controls and is not bound to another data widget
        if (! $widget->getDataWidgetLink()) {
            // add filters
            if ($widget->getData()->hasFilters()) {
                foreach ($widget->getData()->getFilters() as $fltr) {
                    $fltr_html .= $this->getTemplate()->generateHtml($fltr);
                }
                $fltr_html .= <<<HTML

<div id="{$this->getId()}_sizer" style="width:calc(100%/{$this->getNumberOfColumns()});min-width:{$this->getWidthMinimum()}px;"></div>
HTML;
                $this->addOnResizeScript($this->buildJsLayouter() . ';');
                $fltr_html = '<div class="datagrid-filters">' . $fltr_html . '</div>';
            }
            
            // add buttons
            $button_html = $this->buildHtmlButtons();
            
            if ($widget->getHideHeader()) {
                $toolbar_panel_options = ', collapsible: true, collapsed: true';
                $chart_panel_options = ", title: '{$this->getWidget()->getCaption()}'";
            } else {
                $toolbar_panel_options = ", collapsible: true, collapsed: false, title: '{$this->getWidget()->getCaption()}'";
            }
            
            // create a container for the toolbar
            if (($widget->getData()->hasFilters() || $widget->hasButtons())) {
                $toolbar = <<<HTML

        <div data-options="region: 'north', onResize: function(){{$this->getOnResizeScript()}}{$toolbar_panel_options}">
        	<div id="{$this->getToolbarId()}" class="datagrid-toolbar">
        		{$fltr_html}
        		<div style="min-height: 30px;">
        			{$button_html}
        			<a style="position: absolute; right: 0; margin: 0 4px;" href="#" class="easyui-linkbutton" iconCls="icon-search" onclick="{$this->buildJsFunctionPrefix()}doSearch()">{$this->translate('WIDGET.SEARCH')}</a>
        		</div>
        	</div>
        </div>
HTML;
            }
        }
        
        // Create the panel for the chart
        // overflow: hidden loest ein Problem im JavaFX WebView-Browser, bei dem immer wieder
        // Scrollbars ein- und wieder ausgeblendet wurden. Es trat in Verbindung mit Masonry
        // auf, wenn mehrere Elemente auf einer Seite angezeigt wurden (u.a. ein Chart) und
        // das Layout umgebrochen hat. Da sich die Groesse des Charts sowieso an den Container
        // anpasst sollte overflow: hidden keine weiteren Auswirkungen haben.
        $output = <<<HTML

<div class="fitem {$this->getMasonryItemClass()}" style="width:{$this->getWidth()};min-width:{$this->getMinWidth()};height:{$this->getHeight()};padding:{$this->getPadding()};box-sizing:border-box;">
    <div class="easyui-layout" id="{$this->getId()}_wrapper" data-options="fit: true">
    	{$toolbar}
    	<div style="height: auto;" data-options="region: 'center' {$chart_panel_options}">
    		<div id="{$this->getId()}" style="height:calc(100% - 15px); min-height: 100px; overflow: hidden;"></div>
    	</div>
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
        
        // Create the function to process fetched data
        $output .= '
			function ' . $this->buildJsFunctionPrefix() . 'plot(ds){
				';
        
        // Transform the input data to a flot dataset
        foreach ($widget->getSeries() as $series) {
            $series_id = $this->generateSeriesId($series->getId());
            $output .= '
					var ' . $series_id . ' = [];';
            
            if ($series->getChartType() == ChartSeries::CHART_TYPE_PIE) {
                $series_data = $series_id . '[i] = { label: ds.rows[i]["' . $series->getAxisX()->getDataColumn()->getDataColumnName() . '"], data: ds.rows[i]["' . $series->getDataColumn()->getDataColumnName() . '"] }';
            } else {
                // Prepare the code to transform the ajax data to flot data. It will later run in a for loop.
                switch ($series->getChartType()) {
                    case ChartSeries::CHART_TYPE_BARS:
                        $data_key = $series->getDataColumn()->getDataColumnName();
                        $data_value = $series->getAxisY()->getDataColumn()->getDataColumnName();
                        break;
                    default:
                        $data_key = $series->getAxisX()->getDataColumn()->getDataColumnName();
                        $data_value = $series->getDataColumn()->getDataColumnName();
                }
                $series_data .= '
							' . $series_id . '[i] = [ (ds.rows[i]["' . $data_key . '"]' . ($series->getAxisX()->getAxisType() == 'time' ? '*1000' : '') . '), ds.rows[i]["' . $data_value . '"] ];';
            }
        }
        
        // Prepare other flot options
        $series_config = $this->generateSeriesConfig();
        
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
						' . $this->generateSeriesData() . ',
						{
							grid:  { ' . $this->generateGridOptions() . ' }
							, crosshair: {mode: "xy"}
							' . ($axis_y_init ? ', yaxes: [ ' . substr($axis_y_init, 2) . ' ]' : '') . '
							' . ($axis_x_init ? ', xaxes: [ ' . substr($axis_x_init, 2) . ' ]' : '') . '
							' . ($series_config ? ', series: { ' . $series_config . ' }' : '') . '
							, legend: { ' . $this->generateLegendOptions() . ' }
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
        
        // Layout-Funktion hinzufuegen
        $output .= $this->buildJsLayouterFunction();
        
        return $output;
    }

    protected function generateGridOptions()
    {
        return 'hoverable: true';
    }

    protected function generateLegendOptions()
    {
        $output = '';
        if ($this->isPieChart()) {
            $output .= 'show: false';
        } else {
            $output .= 'position: "nw"';
        }
        return $output;
    }

    protected function isPieChart()
    {
        if ($this->getWidget()->getSeries()[0]->getChartType() == ChartSeries::CHART_TYPE_PIE) {
            return true;
        } else {
            return false;
        }
    }

    protected function generateSeriesData()
    {
        $output = '';
        if ($this->isPieChart()) {
            if (count($this->getWidget()->getSeries()) > 1) {
                throw new TemplateUnsupportedWidgetPropertyWarning('The template "' . $this->getTemplate()->getAlias() . '" does not support pie charts with multiple series!');
            }
            
            $output = $this->generateSeriesId($this->getWidget()->getSeries()[0]->getId());
        } else {
            foreach ($this->getWidget()->getSeries() as $series) {
                if ($series->getChartType() == ChartSeries::CHART_TYPE_PIE) {
                    throw new TemplateUnsupportedWidgetPropertyWarning('The template "' . $this->getTemplate()->getAlias() . '" does not support pie charts with multiple series!');
                }
                $series_options = $this->generateSeriesOptions($series);
                $output .= ',
								{
									data: ' . $this->generateSeriesId($series->getId()) . ($series->getChartType() == ChartSeries::CHART_TYPE_BARS ? '.reverse()' : '') . '
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
            
            $url_params = '';
            $url_params .= '&resource=' . $this->getPageId();
            $url_params .= '&element=' . $widget->getData()->getId();
            $url_params .= '&object=' . $widget->getMetaObject()->getId();
            $url_params .= '&action=' . $widget->getLazyLoadingAction();
            
            // send sort information
            if (count($widget->getData()->getSorters()) > 0) {
                foreach ($widget->getData()->getSorters() as $sorter) {
                    $sort .= ',' . urlencode($sorter->attribute_alias);
                    $order .= ',' . urldecode($sorter->direction);
                }
                $url_params .= '&sort=' . substr($sort, 1);
                $url_params .= '&order=' . substr($order, 1);
            }
            
            // send pagination/limit information. Charts currently do not support real pagination, but just a TOP-X display.
            if ($widget->getData()->getPaginate()) {
                $url_params .= '&page=1';
                $url_params .= '&rows=' . (! is_null($widget->getData()->getPaginatePageSize()) ? $widget->getData()->getPaginatePageSize() : $this->getTemplate()->getConfig()->getOption('WIDGET.CHART.PAGE_SIZE'));
            }
            
            // send preset filters
            if ($widget->getData()->hasFilters()) {
                foreach ($widget->getData()->getFilters() as $fnr => $fltr) {
                    if ($fltr->getValue()) {
                        $url_params .= '&fltr' . str_pad($fnr, 2, 0, STR_PAD_LEFT) . '_' . urlencode($fltr->getAttributeAlias()) . '=' . $fltr->getComparator() . urlencode($fltr->getValue());
                    }
                }
            }
            
            // Loader function
            $output .= '
				function ' . $this->buildJsFunctionPrefix() . 'load(urlParams){
					' . $this->buildJsBusyIconShow() . '
					if (!urlParams) urlParams = "";
					$.ajax({
						url: "' . $this->getAjaxUrl() . $url_params . '"+urlParams,
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
            
            // doSearch function with filters for the search button
            $fltrs = array();
            if ($widget->getData()->hasFilters()) {
                foreach ($widget->getData()->getFilters() as $fnr => $fltr) {
                    $fltr_impl = $this->getTemplate()->getElement($fltr, $this->getPageId());
                    $output .= $fltr_impl->generateJs();
                    $fltrs[] = "'&fltr" . str_pad($fnr, 2, 0, STR_PAD_LEFT) . "_" . urlencode($fltr->getAttributeAlias()) . "=" . $fltr->getComparator() . "'+" . $fltr_impl->buildJsValueGetter();
                }
                // build JS for the search function
                $output .= '
						function ' . $this->buildJsFunctionPrefix() . 'doSearch(){
							' . $this->buildJsFunctionPrefix() . "load(" . implode("+", $fltrs) . ');
						}';
            }
            
            // align the filters
            $output .= $this->buildJsLayouter() . ';';
            
            // Call the data loader to populate the Chart initially
            $output .= $this->buildJsFunctionPrefix() . 'load();';
        }
        
        return $output;
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
		        var x = new Date(item.datapoint[0]),
		            y = item.datapoint[1].toFixed(2);
		
		        $("#' . $this->getId() . '_tooltip").html(item.series.xaxis.options.axisLabel + ": " + x.toLocaleDateString() + "<br/>" + item.series.label + ": " + y)
		            .css({top: item.pageY + 5, left: item.pageX + 5})
		            .fadeIn(200);
		      } else {
		        $("#' . $this->getId() . '_tooltip").hide();
		      }
		
		    });
				';
        return $output;
    }

    public function generateSeriesId($string)
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

    public function generateSeriesOptions(ChartSeries $series)
    {
        $options = '';
        switch ($series->getChartType()) {
            case ChartSeries::CHART_TYPE_LINE:
            case ChartSeries::CHART_TYPE_AREA:
                $options = 'lines: 
								{
									show: true,
									' . ($series->getChartType() == ChartSeries::CHART_TYPE_AREA ? 'fill: true' : '') . '
								}';
                break;
            case ChartSeries::CHART_TYPE_BARS:
            case ChartSeries::CHART_TYPE_COLUMNS:
                $options = 'bars: 
								{
									show: true 
									, align: "center"
									' . (! $series->getChart()->getStackSeries() && count($series->getChart()->getSeriesByChartType($series->getChartType())) > 1 ? ', barWidth: 0.2, order: ' . $series->getSeriesNumber() : '') . '
									';
                if ($series->getAxisX()->getAxisType() == ChartAxis::AXIS_TYPE_TIME || $series->getAxisY()->getAxisType() == ChartAxis::AXIS_TYPE_TIME) {
                    $options .= '
									, barWidth: 24*60*60*1000';
                }
                if ($series->getChartType() == ChartSeries::CHART_TYPE_BARS) {
                    $options .= '
									, horizontal: true';
                }
                $options .= '
								}';
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

    protected function generateSeriesConfig()
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
        
        if ($widget->getHeight()->isUndefined() && ($containerWidget = $widget->getParentByType('exface\\Core\\Interfaces\\Widgets\\iContainOtherWidgets')) && ($containerWidget->countWidgetsVisible() > 1)) {
            $widget->setHeight($this->getTemplate()->getConfig()->getOption('WIDGET.CHART.HEIGHT_DEFAULT'));
        }
        return parent::getHeight();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\JqueryLayoutInterface::buildJsLayouterFunction()
     */
    public function buildJsLayouterFunction()
    {
        $output = <<<JS

    function {$this->getId()}_layouter() {
        $("#{$this->getToolbarId()} .datagrid-filters").masonry({
            columnWidth: "#{$this->getId()}_sizer",
            itemSelector: ".{$this->getId()}_masonry_fitem"
        });
    }
JS;
        
        return $output;
    }

    /**
     * Returns the default number of columns to layout this widget.
     *
     * @return integer
     */
    public function getDefaultColumnNumber()
    {
        return $this->getTemplate()->getConfig()->getOption("WIDGET.CHART.COLUMNS_BY_DEFAULT");
    }

    /**
     * Returns if the the number of columns of this widget depends on the number of columns
     * of the parent layout widget.
     *
     * @return boolean
     */
    public function inheritsColumnNumber()
    {
        return true;
    }
    
    protected function getMoreButtonsMenuCaption(){
        return '...';
    }
}
?>