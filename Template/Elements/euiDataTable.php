<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\DataTable;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryDataTableTrait;
use exface\Core\Interfaces\Actions\iReadData;
use exface\Core\Widgets\Tabs;

/**
 *
 * @author Andrej Kabachnik
 *        
 * @method DataTable getWidget()
 *        
 */
class euiDataTable extends euiData
{
    
    use JqueryDataTableTrait;

    protected function init()
    {
        parent::init();
        $this->setElementType('datagrid');
        $widget = $this->getWidget();
        
        // Take care of refresh links
        if ($refresh_link = $widget->getRefreshWithWidget()) {
            if ($refresh_link_element = $this->getTemplate()->getElement($refresh_link->getWidget())) {
                $refresh_link_element->addOnChangeScript($this->buildJsRefresh());
            }
        }
        
        $widget->getConfiguratorWidget()
            ->setTabPosition(Tabs::TAB_POSITION_RIGHT)
            ->setHideTabsCaptions(true);
    }

    public function generateHtml()
    {
        $widget = $this->getWidget();
        
        // Prepare the header with the configurator and the toolbars
        $configurator_widget = $widget->getConfiguratorWidget();
        /* @var $configurator_element \exface\JEasyUiTemplate\Template\Elements\euiDataConfigurator */
        $configurator_element = $this->getTemplate()->getElement($this->getWidget()->getConfiguratorWidget())->setFitOption(false)->setStyleAsPills(true);
        
        if ($configurator_widget->isEmpty()){
            $configurator_widget->setHidden(true);
            $configurator_panel_collapsed = ', collapsed: true';
        }
        
        if ($widget->getHideHeader()){
            $header_style = 'visibility: hidden; height: 0px; padding: 0px;';
        }
        
        // jEasyUI will not resize the configurator once the datagrid is resized
        // (don't know why), so we need to do it manually.
        // Wrapping the resize-call into a setTimeout( ,0) is another strange
        // workaround, but if not done so, the configurator will get resized to
        // the old size, not the new one.
        $this->addOnResizeScript("
            if(typeof $('#" . $configurator_element->getId() . "')." . $configurator_element->getElementType() . "() !== 'undefined') {
                setTimeout(function(){
                    $('#" . $configurator_element->getId() . "')." . $configurator_element->getElementType() . "('resize');
                }, 0);
            }
        ");
        
        // Create a context menu if any items were found
        $context_menu_html = $this->buildHtmlContextMenu();
        if ($context_menu_html && $widget->getContextMenuEnabled()) {
            $context_menu_html .= '<div id="' . $this->getId() . '_cmenu" class="easyui-menu">' . $context_menu_html . '</div>';
        } else {
            $context_menu_html = '';
        }
        
        // Create the search button
        if (! $widget->getHideRefreshButton()){
            $search_button = '<button type="submit" style="position: absolute; right: 0; margin: 0 4px;" href="#" class="easyui-linkbutton" iconCls="fa fa-search">' . $this->translate('WIDGET.SEARCH') . '</button>';
        }
        
        $output .= <<<HTML
            <{$this->getBaseHtmlElement()} id="{$this->getId()}"></{$this->getBaseHtmlElement()}>
            <div id="{$this->getToolbarId()}" style="{$header_style}">
                <div class="easyui-panel exf-data-header" data-options="footer: '#{$this->getToolbarId()}_footer', border: false, width: '100%' {$configurator_panel_collapsed}">
                    {$configurator_element->generateHtml()}
                </div>
                <div id="{$this->getToolbarId()}_footer" class="exf-toolbar exf-data-toolbar">
                    {$this->buildHtmlButtons()}
                    {$search_button}
                </div>
            </div>
            {$context_menu_html}
HTML;
        
        return $this->buildHtmlWrapper($output);
    }

    public function generateJs()
    {
        $widget = $this->getWidget();
        $output = '';
        
        if ($this->isEditable()) {
            foreach ($this->getEditors() as $editor) {
                $output .= $editor->buildJsInlineEditorInit();
            }
        }
        
        $grid_head = '';
        
        // add dataGrid specific params
        // row details (expandable rows)
        if ($widget->hasRowDetails()) {
            // Create a detail container
            /* @var $details \exface\Core\Widgets\container */
            $details = $widget->getRowDetailsContainer();
            $details_element = $this->getTemplate()->getElement($widget->getRowDetailsContainer());
            $details_height = (! $details->getHeight()->isUndefined() ? ", height: '" . $details_element->getHeight() . "'" : "");
            
            // Add the needed options to our datagrid
            $grid_head .= <<<JS
					, view: detailview
					, detailFormatter: function(index,row){
						return '<div id="{$details_element->getId()}_'+row.{$widget->getMetaObject()->getUidAlias()}+'"></div>';
					}
					, onExpandRow: function(index,row){
						$('#{$details_element->getId()}_'+row.{$widget->getMetaObject()->getUidAlias()}).panel({
			            	border: false,
							href: '{$this->getAjaxUrl()}',
			            	method: 'post',
							queryParams: {
								action: '{$widget->getRowDetailsAction()}',
								resource: '{$this->getPageId()}',
								element: '{$details->getId()}',
								prefill: {
									oId: "{$widget->getMetaObjectId()}", 
									rows:[
										{ {$widget->getMetaObject()->getUidAlias()} : row.{$widget->getMetaObject()->getUidAlias()} }
									], 
									filters: {$this->buildJsDataFilters()}
								},
								exfrid: row.{$widget->getMetaObject()->getUidAlias()}
							},
							onLoad: function(){
			                   	$('#{$this->getId()}').{$this->getElementType()}('fixDetailRowHeight',index);
			            	},
			                onLoadError: function(response){
			                	{$this->buildJsShowError('response.responseText', 'response.status + " " + response.statusText')} 
							},
			       			onResize: function(){
			                	$('#{$this->getId()}').{$this->getElementType()}('fixDetailRowHeight',index);			
                    		}
			         	{$details_height}
						});
					}
JS;
        }
        
        // group rows if required
        if ($widget->hasRowGroups()) {
            $grid_head .= ', view: groupview' . ",groupField: '" . $widget->getRowGroupsByColumnId() . "'" . ",groupFormatter:function(value,rows){ return value" . ($widget->getRowGroupsShowCount() ? " + ' (' + rows.length + ')'" : "") . ";}";
            if ($widget->getRowGroupsExpand() == 'none' || $widget->getRowGroupsExpand() == 'first') {
                $this->addOnLoadSuccess("$('#" . $this->getId() . "')." . $this->getElementType() . "('collapseGroup');");
            }
            if ($widget->getRowGroupsExpand() == 'first') {
                $this->addOnLoadSuccess("$('#" . $this->getId() . "')." . $this->getElementType() . "('expandGroup', 0);");
            }
        }
        
        // Double click actions. Currently only supports one double click action - the first one in the list of buttons
        if ($dblclick_button = $widget->getButtonsBoundToMouseAction(EXF_MOUSE_ACTION_DOUBLE_CLICK)[0]) {
            $grid_head .= ', onDblClickRow: function(index, row) {' . $this->getTemplate()->getElement($dblclick_button)->buildJsClickFunction() . '}';
        }
        
        // Left click actions. Currently only supports one double click action - the first one in the list of buttons
        if ($leftclick_button = $widget->getButtonsBoundToMouseAction(EXF_MOUSE_ACTION_LEFT_CLICK)[0]) {
            $grid_head .= ', onClickRow: function(index, row) {' . $this->getTemplate()->getElement($leftclick_button)->buildJsClickFunction() . '}';
        }
        
        // Right click actions or context menu
        if ($rightclick_button = $widget->getButtonsBoundToMouseAction(EXF_MOUSE_ACTION_RIGHT_CLICK)[0]) {
            $grid_head .= ', onClickRow: function(index, row) {' . $this->getTemplate()->getElement($rightclick_button)->buildJsClickFunction() . '}';
        } else {
            // Context menu
            if ($widget->getContextMenuEnabled()) {
                $grid_head .= ', onRowContextMenu: function(e, index, row) {
    					e.preventDefault();
    					e.stopPropagation();
    					$(this).' . $this->getElementType() . '("selectRow", index);
    	                $("#' . $this->getId() . '_cmenu").menu("show", {
    	                    left: e.pageX,
    	                    top: e.pageY
    	                });
    	                return false;
    				}';
            }
        }
        
        if ($this->isEditable()) {
            $changes_col_array = array();
            $this->addOnLoadSuccess($this->buildJsEditModeEnabler());
            // add data and changes getter if the grid is editable
            $output .= "
						function " . $this->buildJsFunctionPrefix() . "getData(){
							var data = [];
							var rows = $('#" . $this->getId() . "')." . $this->getElementType() . "('getRows');
							for (var i=0; i<rows.length; i++){
								$('#" . $this->getId() . "')." . $this->getElementType() . "('endEdit', i);
								data[$('#" . $this->getId() . "')." . $this->getElementType() . "('getRowIndex', rows[i])] = rows[i];
							}
							return data;
						}";
            foreach ($this->getEditors() as $col_id => $editor) {
                $col = $widget->getColumn($col_id);
                // Skip editors for columns, that are not attributes
                if (! $col->getAttribute())
                    continue;
                    // For all other editors, that belong to related attributes, add some JS to update all rows with that
                    // attribute, once the value of one of them changes. This makes sure, that the value of a related attribute
                    // is the same, even if it is shown in multiple rows at all times!
                $rel_path = $col->getAttribute()->getRelationPath();
                if ($rel_path && ! $rel_path->isEmpty()) {
                    $col_obj_uid = $rel_path->getRelationLast()->getRelatedObjectKeyAttribute()->getAliasWithRelationPath();
                    $this->addOnLoadSuccess("$('td[field=\'" . $col->getDataColumnName() . "\'] input').change(function(){
						var rows = $('#" . $this->getId() . "')." . $this->getElementType() . "('getRows');
						var thisRowIdx = $(this).parents('tr.datagrid-row').attr('datagrid-row-index');
						var thisRowUID = rows[thisRowIdx]['" . $col_obj_uid . "'];
						for (var i=0; i<rows.length; i++){
							if (rows[i]['" . $col_obj_uid . "'] == thisRowUID){
								var ed = $('#" . $this->getId() . "')." . $this->getElementType() . "('getEditor', {index: i, field: '" . $col->getDataColumnName() . "'});
								$(ed.target)." . $editor->buildJsValueSetterMethod("$(this)." . $editor->buildJsValueGetterMethod()) . ";
							}
						}
					});");
                }
                
                $changes_col_array[] = $widget->getColumn($col_id)->getDataColumnName();
            }
            
            $changes_cols = implode("','", $changes_col_array);
            
            if ($changes_cols)
                $changes_cols = "'" . $changes_cols . "'";
            
            foreach ($widget->getColumnsWithSystemAttributes() as $col) {
                $changes_cols .= ",'" . $col->getDataColumnName() . "'";
            }
            $changes_cols = trim($changes_cols, ',');
            
            $output .= "
						function " . $this->buildJsFunctionPrefix() . "getChanges(){
							var data = [];
							var cols = [" . $changes_cols . "];
							var rowCount = $('#" . $this->getId() . "')." . $this->getElementType() . "('getRows').length;
							for (var i=0; i<rowCount; i++){
								$('#" . $this->getId() . "')." . $this->getElementType() . "('endEdit', i);
							}
							rows = $('#" . $this->getId() . "')." . $this->getElementType() . "('getChanges');
							for (var i=0; i<rows.length; i++){
								$('#" . $this->getId() . "')." . $this->getElementType() . "('endEdit', i);
								var row = {};
								for (var j=0; j<cols.length; j++){
									row[cols[j]] = rows[i][cols[j]];
								}
								data.push(row);
							}
							return data;
						}";
        }
        
        // get the standard params for grids and put them before the custom grid head
        $grid_head = $this->buildJsInitOptions() . $grid_head;
        // Auf manchen Seiten (z.B. Kundenreklamation) kam es nach dem Laden zu Fehlern im Layout
        // (Tabelle nimmt nicht den gesamten verfügbaren Raum ein -> weißer Rand darunter, Spalten-
        // Header sind schmaler als die Inhalte -> verschoben). Durch den Aufruf von "autoSizeColumn"
        // onResize wird das Layout nach dem Laden oder ausklappen der SideBar erneuert. (Auch
        // möglich wäre ein Aufruf von "resize" (dann werden aber die Spaltenbreiten nicht
        // korrigiert) oder "autoSizeColumn" onLoadSuccess ($this->addOnLoadSuccess()) und
        // onLoadError u.U. mit setTimeout()). Durch diese Aenderung wird das Layout leider etwas
        // traeger.
        $resize_function = $this->getOnResizeScript();
        $resize_function .= '
					$("#' . $this->getId() . '").' . $this->getElementType() . '("autoSizeColumn");';
        $grid_head .= ', fit: true
				, onResize: function(){' . $resize_function . '}' . ($this->getOnChangeScript() ? ', onSelect: function(index, row){' . $this->getOnChangeScript() . '}' : '') . ($widget->getCaption() ? ', title: "' . str_replace('"', '\"', $widget->getCaption()) . '"' : '');
        
        // instantiate the data grid
        $output .= '
            $("#' . $this->getId() . '").' . $this->getElementType() . '({' . $grid_head . '});
        ';
        
        // Add Scripts for the configurator widget
        $output .= $this->getTemplate()->getElement($widget->getConfiguratorWidget())->generateJs();
        $fltrs = array();
        if ($widget->hasFilters()) {
            foreach ($widget->getFilters() as $fnr => $fltr) {
                $fltr_impl = $this->getTemplate()->getElement($fltr, $this->getPageId());
                //$output .= $fltr_impl->generateJs();
                $fltrs[] = '"fltr' . str_pad($fnr, 2, 0, STR_PAD_LEFT) . '_' . urlencode($fltr->getAttributeAlias()) . '": "' . $fltr->getComparator() . '"+' . $fltr_impl->buildJsValueGetter();
            }
        }
        // build JS for the search function
        $output .= '
						function ' . $this->buildJsFunctionPrefix() . 'doSearch(){
							$("#' . $this->getId() . '").' . $this->getElementType() . '("load",{' . (count($fltrs) > 0 ? implode(', ', $fltrs) . ',' : '') . 'action: "' . $widget->getLazyLoadingAction() . '", resource: "' . $this->getPageId() . '", element: "' . $this->getWidget()->getId() . '"});
						}';
        
        // build JS for the button actions
        $output .= $this->buildJsButtons();
        
        // Add buttons to the pager at the bottom of the datagrid
        $bottom_buttons = array();
        
        // If the top toolbar is hidden, add actions to the bottom toolbar
        if ($widget->getHideHeader() && ! $widget->getHideFooter() && $widget->hasButtons()) {
            foreach ($widget->getButtons() as $button) {
                if ($button->isHidden())
                    continue;
                if ($button->getAction()->getInputRowsMin() == 0) {
                    $bottom_buttons[] = '{
						iconCls:  "' . $this->buildCssIconClass($button->getIconName()) . '",
						title: "' . str_replace('"', '\"', $button->getCaption()) . '",
						handler: ' . $this->getTemplate()->getElement($button)->buildJsClickFunctionName() . '
					}';
                }
            }
        }
        
        // Add the help button in the bottom toolbar
        if (! $widget->getHideHelpButton()) {
            $output .= $this->getTemplate()->generateJs($widget->getHelpButton());
            $bottom_buttons[] = '{
						iconCls:  "fa fa-question-circle-o",
						title: "' . $this->translate('HELP') . '",
						handler: ' . $this->getTemplate()->getElement($widget->getHelpButton())->buildJsClickFunctionName() . '
					}';
        }
        
        if (! empty($bottom_buttons)) {
            $output .= '
					
							var pager = $("#' . $this->getId() . '").' . $this->getElementType() . '("getPager");
	            			pager.pagination({
								buttons: [' . implode(', ', $bottom_buttons) . ']
							});
										
					';
        }
        
        return $output;
    }

    public function buildJsEditModeEnabler()
    {
        return '
					var rows = $(this).' . $this->getElementType() . '("getRows");
					for (var i=0; i<rows.length; i++){
						$(this).' . $this->getElementType() . '("beginEdit", i);
					}
				';
    }

    /**
     * The getter will return the value of the UID column of the selected row by default.
     * If the parameter row is
     * specified, it will return the UID column of that row. Specifying the column parameter will result in returning
     * the value of that column in the specified row or (if row is not set) the selected row.
     * IDEA perhaps it should return an entire row as an array if the column is not specified. Just have a feeling, it
     * might be better...
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\jeasyuiAbstractWidget::buildJsValueGetter()
     */
    public function buildJsValueGetter($column = null, $row = null)
    {
        $output = "$('#" . $this->getId() . "')";
        if (is_null($row)) {
            $output .= "." . $this->getElementType() . "('getSelected')";
        }
        if (is_null($column)) {
            $column = $this->getWidget()->getMetaObject()->getUidAlias();
        }
        return "(" . $output . " ? " . $output . "['" . $column . "'] : '')";
    }

    public function buildJsChangesGetter()
    {
        if ($this->isEditable()) {
            $output = $this->buildJsFunctionPrefix() . "getChanges()";
        } else {
            $output = "[]";
        }
        return $output;
    }

    public function buildJsDataGetter(ActionInterface $action = null)
    {
        $rows = '';
        $filters = '';
        if (is_null($action)) {
            $rows = "$('#" . $this->getId() . "')." . $this->getElementType() . "('getData')";
        } elseif ($action instanceof iReadData) {
            foreach ($this->getWidget()->getFilters() as $filter) {
                $filters .= ', ' . $this->getTemplate()->getElement($filter)->buildJsConditionGetter();
            }
            $filters = $filters ? '{operator: "AND", conditions: [' . trim($filters, ",") . ']}' : '';
        } elseif ($this->isEditable() && $action->implementsInterface('iModifyData')) {
            if ($this->getWidget()->getMultiSelect()) {
                $rows = "$('#" . $this->getId() . "')." . $this->getElementType() . "('getSelections').length > 0 ? $('#" . $this->getId() . "')." . $this->getElementType() . "('getSelections') : " . $this->buildJsFunctionPrefix() . "getChanges()";
            } else {
                $rows = $this->buildJsFunctionPrefix() . "getChanges()";
            }
        } else {
            $rows = "$('#" . $this->getId() . "')." . $this->getElementType() . "('getSelections')";
        }
        return "{oId: '" . $this->getWidget()->getMetaObjectId() . "'" . ($rows ? ", rows: " . $rows : '') . ($filters ? ", filters: " . $filters : "") . "}";
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::buildJsRefresh()
     */
    public function buildJsRefresh($keep_pagination_position = false)
    {
        if ($keep_pagination_position) {
            return '$("#' . $this->getId() . '").' . $this->getElementType() . '("reload")';
        } else {
            return $this->buildJsFunctionPrefix() . 'doSearch()';
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::generateHeaders()
     */
    public function generateHeaders()
    {
        $includes = parent::generateHeaders();
        // Masonry is neede to align filters nicely
        $includes[] = '<script type="text/javascript" src="exface/vendor/bower-asset/masonry/dist/masonry.pkgd.min.js"></script>';
        // Row details view
        if ($this->getWidget()->hasRowDetails()) {
            $includes[] = '<script type="text/javascript" src="exface/vendor/exface/JEasyUiTemplate/Template/js/jeasyui/extensions/datagridview/datagrid-detailview.js"></script>';
        }
        /*
         * IDEA The row groups get included always by the current template. Perhaps we need some way to allow manual includes in parallel with automatic ones
         * if ($this->getWidget()->hasRowGroups()){
         * $includes[] = '<script type="text/javascript" src="exface/vendor/exface/JEasyUiTemplate/Template/js/jeasyui/datagridview/datagrid-groupview.js"></script>';
         * }
         */
        return $includes;
    }

    /*
     * public function buildJsInitOptionsHead(){
     * /* @var $widget exface\Core\Widgets\DataTable
     */
    /*
     * $widget = $this->getWidget();
     * $output = parent::buildJsInitOptionsHead();
     * $output .= ', fit: true'
     * . ($widget->getCaption() ? ', title: "' . $widget->getCaption() . '"' : '')
     * ;
     * return $output;
     * }
     */
    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\euiAbstractElement::getHeight()
     */
    function getHeight()
    {
        // Die Hoehe der DataTable passt sich nicht automatisch dem Inhalt an. Wenn sie also
        // nicht den gesamten Container ausfuellt, kollabiert sie so dass die Datensaetze nicht
        // mehr sichtbar sind (nur noch Header und Footer). Deshalb wird hier die Hoehe der
        // DataTable gesetzt, wenn sie nicht definiert ist, und sie nicht alleine im Container
        // ist.
        $widget = $this->getWidget();
        
        if ($widget->getHeight()->isUndefined() && ($containerWidget = $widget->getParentByType('exface\\Core\\Interfaces\\Widgets\\iContainOtherWidgets')) && ($containerWidget->countWidgetsVisible() > 1)) {
            $widget->setHeight($this->getTemplate()->getConfig()->getOption('WIDGET.DATATABLE.HEIGHT_DEFAULT'));
        }
        return parent::getHeight();
    }
}
?>