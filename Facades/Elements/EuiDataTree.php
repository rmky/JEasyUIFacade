<?php
namespace exface\JEasyUIFacade\Facades\Elements;

use exface\Core\Widgets\DataTree;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\JEasyUIFacade\Facades\JEasyUIFacade;
use exface\Core\Interfaces\DataSheets\DataSheetInterface;
use exface\Core\Interfaces\WidgetInterface;
use exface\Core\DataTypes\SortingDirectionsDataType;
use exface\Core\Factories\ActionFactory;
use exface\Core\Actions\UpdateData;
use exface\Core\DataTypes\ComparatorDataType;

/**
 * @method DataTree getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class EuiDataTree extends EuiDataTable
{

    private $on_expand = '';

    protected function init()
    {
        parent::init();
        $this->setElementType('treegrid');
        
        if ($this->getWidget()->getTreeLeafIdColumnId() !== null) {
            $leafIdCol = $this->getWidget()->getColumn($this->getWidget()->getTreeLeafIdColumnId());
            if (! $leafIdCol->getDataColumnName()) {
                $leafIdCol->setDataColumnName('_leafId');
            }
        }
    }
    
    public function buildHtmlHeadTags()
    {
        $includes = parent::buildHtmlHeadTags();
        if ($this->getWidget()->hasRowReorder() === true) {
            $includes[] = '<script type="text/javascript" src="' . $this->getFacade()->buildUrlToSource('LIBS.JEASYUI.EXTENSIONS.TREEGRID_DND') . '"></script>';
        }
        return $includes;
    }

    public function buildJsInitOptionsHead()
    {        
        $widget = $this->getWidget();
        $leafIdColumnName = $widget->getTreeLeafIdColumn()->getDataColumnName();
        
        if ($this->isEditable()) {
            $this->addOnExpand('
					if (row){
						var rows = $(this).' . $this->getElementType() . '("getChildren", row.' . $leafIdColumnName . ');
						for (var i=0; i<rows.length; i++){
							$(this).' . $this->getElementType() . '("beginEdit", rows[i].' . $leafIdColumnName . ');
						}
					}
					');
        }
        
        if (($leafIdDelim = $widget->getTreeLeafIdConcatenate()) !== null) {
            $calculatedIdField = ', idField: "_leafId"';
            $leafIdCalcScript = 'data.rows[row]["_leafId"] = (parentId ? parentId+"' . $leafIdDelim . '" : "")+data.rows[row]["' . $widget->getUidColumn()->getDataColumnName() . '"];';
        }
        
        $grid_head = parent::buildJsInitOptionsHead() . $calculatedIdField;
        $grid_head .= <<<JS
        
                        , treeField: '{$widget->getTreeColumn()->getDataColumnName()}'
                        , lines: false
                        , loadFilter: function(data, parentId) {
                            
                            var row = 0;
                            if ("rows" in data) {
                                var rowCnt = data.rows.length;
                                var field, parentRow;
                                
                                for (row=0; row<rowCnt; row++) {
                                    if (parentId !== null && parentId !== undefined) {
                                        data.rows[row]["_parentId"] = parentId;
                                    } else {
                                    }
                                    {$leafIdCalcScript}
                                }
                            } else {
                                if (parentId !== null && parentId !== undefined) {
                                    data[0]["_parentId"] = parentId;
                                } else {
                                    if (data[0]['{$widget->getTreeParentIdAttributeAlias()}'] !== null && data[0]['{$widget->getTreeParentIdAttributeAlias()}'] !== undefined ) {
                                        data[0]["_parentId"] = data[0]['{$widget->getTreeParentIdAttributeAlias()}'];
                                    }
                                }
                            }

                            return data;
                        }
                        {$this->buildJsDragNDropInitOptions()}
                        {$this->buildJsOnLoadSuccessOption()}                        

JS;
                        
        $grid_head .= ($this->buildJsOnExpandScript() ? ', onExpand: function(row){' . $this->buildJsOnExpandScript() . '}' : '');

        return $grid_head;
    }
    
    protected function buildJsDragNDropInitOptions() : string
    {
        $widget = $this->getWidget();
        
        if ($widget->hasRowReorder() === false) {
            return '';   
        }
        
        //Enable Drag and Drop
        $this->addOnLoadSuccess("$('#{$this->getId()}').{$this->getElementType()}('enableDnd', null);");
        
        $reorderPart = $widget->getRowReorder();
        $direction = $reorderPart->getOrderDirection();
        $directionASC = SortingDirectionsDataType::ASC;
        $directionDESC = SortingDirectionsDataType::DESC;
        $indexColName = $widget->getColumnByAttributeAlias($reorderPart->getOrderIndexAttributeAlias())->getDataColumnName();
        $uidColName = $widget->getUidColumn()->getDataColumnName();
        
        $headers = ! empty($this->getAjaxHeaders()) ? 'headers: ' . json_encode($this->getAjaxHeaders()) . ',' : '';
        $rowReorderScript = <<<JS
        
                            if (changedRows.length > 0) {
                                // when row gets moved to new parent by dropping it on parent row, index will be set the highest index + 1
                                var children = parent['children'];
                                var count = children.length;
                                if (point === 'append') {
                                    if (count === 1) {
                                        changedRows[0]['{$indexColName}'] = 0;
                                    } else if (count > 1) {
                                        if ('{$direction}' === '{$directionASC}') {
                                            changedRows[0]['{$indexColName}'] = parseInt(children[count - 2]['{$indexColName}']) + 1;
                                        }
                                        if ('{$direction}' === '{$directionDESC}') {
                                            changedRows[0]['{$indexColName}'] = parseInt(children[0]['{$indexColName}']) + 1;
                                        }
                                    }
                                    
                                // if row gets moved to a certain point above or below another row
                                } else {
                                    var targetRowIndex, targetRowPosition, sourceRowIndex;
                                    children.sort(function (a, b) {return a['{$indexColName}']-b['{$indexColName}']});
                                    if ('{$direction}' === '{$directionDESC}') {
                                        if (point === 'top') {
                                            point = 'bottom';
                                        } else {
                                            point = 'top';
                                        }
                                    }
                                    
                                    // get position of the targetRow
                                    for (var i = 0; i < count; i++) {
                                        if (children[i]['{$uidColName}'] === targetRow['{$uidColName}']) {
                                            targetRowIndex = i;
                                            targetRowPosition = parseInt(targetRow['{$indexColName}']);
                                            break;
                                        }
                                    }
                                    
                                    // if the sourceRow had same parent as the targetRow search for old sourceRow position
                                    if (targetRow['{$widget->getTreeParentIdAttributeAlias()}'] === sourceRow['{$widget->getTreeParentIdAttributeAlias()}']) {
                                        for (var i = 0; i < count; i++) {
                                            if (children[i]['{$uidColName}'] === sourceRow['{$uidColName}']) {
                                                sourceRowIndex = i;
                                                break;
                                            }
                                        }
                                    }
                                    
                                    // if the sourceRow was moved from another parent
                                    if (sourceRowIndex == undefined) {
                                        if (point === 'top') {
                                            changedRows[0]['{$indexColName}'] = targetRowPosition;
                                            var start = targetRowIndex;
                                        } else {
                                            changedRows[0]['{$indexColName}'] = targetRowPosition + 1;
                                            var start = targetRowIndex + 1;
                                        }
                                        // as the sourceRow gets already added to the children array, skip element when UID attribute value is same as sourceRow
                                        for (var i = start; i < count; i++) {
                                            if (sourceRow['{$uidColName}'] === children[i]['{$uidColName}']) {
                                                continue;
                                            }
                                            var row = {};
                                            row['{$uidColName}'] = children[i]['{$uidColName}'];
                                            row['{$widget->getTreeParentIdAttributeAlias()}'] = children[i]['{$widget->getTreeParentIdAttributeAlias()}']
                                            row['{$indexColName}'] = parseInt(children[i]['{$indexColName}']) + 1;
                                            changedRows.push(row);
                                        }
                                        
                                    // if sourceRow had same parent as targetRow
                                    } else {
                                        if (sourceRowIndex < targetRowIndex) {
                                            if (point === 'top') {
                                                changedRows[0]['{$indexColName}'] = targetRowPosition - 1;
                                                var end = targetRowIndex - 1;
                                            } else {
                                                changedRows[0]['{$indexColName}'] = targetRowPosition;
                                                var end = targetRowIndex;
                                            }
                                            for (i = sourceRowIndex + 1; i <= end; i++) {
                                                var row = {};
                                                row['{$uidColName}'] = children[i]['{$uidColName}'];
                                                row['{$indexColName}'] = parseInt(children[i]['{$indexColName}']) - 1 ;
                                                changedRows.push(row);
                                            }
                                        } else {
                                            if (point === 'top') {
                                                changedRows[0]['{$indexColName}'] = targetRowPosition;
                                                var start = targetRowIndex;
                                            } else {
                                                changedRows[0]['{$indexColName}'] = targetRowPosition + 1;
                                                var start = targetRowIndex + 1;
                                            }
                                            for (i = start; i < sourceRowIndex; i++) {
                                                var row = {};
                                                row['{$uidColName}'] = children[i]['{$uidColName}'];
                                                row['{$indexColName}'] = parseInt(children[i]['{$indexColName}']) + 1 ;
                                                changedRows.push(row);
                                            }
                                        }
                                    }
                                }
                            }
                            
JS;
        
        
        // Add system attributes to all rows
        $keepColumns = [];
        foreach ($widget->getColumns() as $col) {
            if ($col->isBoundToAttribute() && $col->getAttribute()->isSystem()) {
                $keepColumns[] = $col->getDataColumnName();
            }
        }
        $systemColumnsArrayJs = json_encode($keepColumns); 
        
        return <<<JS

                        , onDrop: function(targetRow, sourceRow, point) {
                            var aSystemCols = $systemColumnsArrayJs;
                            var changedRows = [];
                            var row = {};
                            var data = $('#{$this->getId()}').{$this->getElementType()}("getData");
                            row['{$uidColName}'] = sourceRow['{$uidColName}'];
                            if (targetRow === null && point === 'append') {                                
                                row['{$widget->getTreeParentIdAttributeAlias()}'] = data[0]['{$widget->getTreeParentIdAttributeAlias()}'];
                                if (sourceRow['{$widget->getTreeParentIdAttributeAlias()}'] !== data[0]['{$widget->getTreeParentIdAttributeAlias()}']) {
                                    changedRows.push(row);
                                }                               
                            } else {                                                       
                                if (sourceRow["_parentId"] == undefined) {
                                    if (point === 'append') {
                                        sourceRow["_parentId"] = targetRow['{$uidColName}'];
                                    } else {
                                        if (targetRow['{$widget->getTreeParentIdAttributeAlias()}'] !== undefined) {
                                            sourceRow["_parentId"] = targetRow['{$widget->getTreeParentIdAttributeAlias()}'];
                                        } else {
                                            sourceRow["_parentId"] = 0;
                                        }  
                                    }
                                }
                                if (sourceRow['{$widget->getTreeParentIdAttributeAlias()}'] !== sourceRow["_parentId"]) {                                                                
                                    row['{$widget->getTreeParentIdAttributeAlias()}'] = sourceRow['_parentId'];
                                }                            
                                if (! (point === 'append' && sourceRow['{$widget->getTreeParentIdAttributeAlias()}'] === targetRow['{$uidColName}'])) {
                                    changedRows.push(row);
                                }
                            }
                            var dataGetter = {$this->buildJsDataGetter(ActionFactory::createFromString($this->getWorkbench(), UpdateData::class,$widget))};
                            var parent = $("#{$this->getId()}").{$this->getElementType()}('getParent', sourceRow['{$uidColName}']);
                            if (parent === null) {
                                parent  = {};
                                parent['children'] = data;
                            }
                            {$rowReorderScript}
                            dataGetter['rows'] = changedRows;

                            $.ajax({
								type: 'POST',
								url: '{$this->getAjaxUrl()}',
                                {$headers} 
								data: {	
									action: 'exface.Core.UpdateData',
									resource: '{$widget->getPage()->getAliasWithNamespace()}',
									element: '{$widget->getId()}',
									object: '{$widget->getMetaObject()->getId()}',
									data: dataGetter
								},
								success: function(data, textStatus, jqXHR) {
                                    if (typeof data === 'object') {
                                        response = data;
                                    } else {
                                        var response = {};
    									try {
    										response = $.parseJSON(data);
    									} catch (e) {
    										response.error = data;
    									}
                                    }
				                   	if (response.success){
										$("#{$this->getId()}").{$this->getElementType()}("reload");
				                    } else {
										{$this->buildJsShowMessageError('response.error', '"Server error"')}
				                    }
								},
								error: function(jqXHR, textStatus, errorThrown){ 
									{$this->buildJsShowError('jqXHR.responseText', 'jqXHR.status + " " + jqXHR.statusText')}
								}
							});                                
                        }
JS;
    }

    public static function buildResponseData(JEasyUIFacade $facade, DataSheetInterface $data_sheet, WidgetInterface $widget)
    {
        $result = $facade->buildResponseData($data_sheet);
        /* @var $widget \exface\Core\Widgets\DataTree */
        $folderFlagCol = $widget->hasTreeFolderFlag() ? $widget->getTreeFolderFlagAttributeAlias() : null;
        $parentCol = $widget->getTreeParentIdAttributeAlias();
        $idCol = $widget->getUidColumn()->getDataColumnName();
        $rowsById = [];
        foreach ($result['rows'] as $nr => $row) {
            // If we know, which attribute flags a leaf as a folder, use it to set the node state (open/close)
            if ($folderFlagCol !== null) {
                if ($row[$folderFlagCol]) {
                    // $result['rows'][$nr]['state'] = $row[$this->getWidget()->getTreeFolderFlagAttributeAlias()] ? 'closed' : 'open';
                    $result['rows'][$nr]['state'] = 'closed';
                    // Dirty hack to remove zero numeric values on folders, because they are easily assumed to be sums
                    foreach ($row as $fld => $val) {
                        if (is_numeric($val) && intval($val) == 0) {
                            $result['rows'][$nr][$fld] = '';
                        }
                    }
                } else {
                    $result['rows'][$nr]['state'] = 'open';
                }
                
                unset($result['rows'][$nr][$folderFlagCol]);
            } else {
                // If we can't tell, if a node has children - make it close (assume it may have children)
                $result['rows'][$nr]['state'] = 'closed';
            }
            
            // The jEasyUI treegrid cannot build trees itself, so we need to form a hierarchy here, if we have
            // parents and their children within our data.
            // We save references to all rows in an array indexed with row UIDs
            $rowsById[$row[$idCol]] =& $result['rows'][$nr];
        }
        
        // The jEasyUI treegrid cannot build trees itself, so we need to form a hierarchy here, if we have
        // parents and their children within our data.
        $rowCnt = count($result['rows']);
        for ($nr = $rowCnt-1; $nr >= 0; $nr--) {
            $row = $result['rows'][$nr];
            $parentId = $row[$parentCol];
            
            // Now, if the parent id is found in our array, we need to remove the row from the flat data array
            // and put it into the children-array of it's parent row. We need to use references here as the
            // next row may be a child of one of the children in-turn.
            if ($rowsById[$parentId] !== null) {                
                if ($rowsById[$parentId]['children'] === null) {
                    //add children array to parent, add row as child
                    $rowsById[$parentId]['children'][] =& $result['rows'][$nr];;
                } else {
                    //add row as the first object in children array to parent
                    $val =& $result['rows'][$nr];
                    array_unshift($rowsById[$parentId]['children'],  $val);
                }
                    $rowsById[$parentId]['state'] = 'open';
                    //set new reference for the row, as current reference will be unset
                    $rowsById[$row[$idCol]] =& $rowsById[$parentId]['children'][0];
                unset ($result['rows'][$nr]);
            }
        }
        
        // Get rid of gaps in row numbers
        $result['rows'] = array_values($result['rows']);
        
        $result['footer'][0][$widget->getTreeColumn()->getDataColumnName()] = '';
        
        return $result;
    }

    public function buildJsEditModeEnabler()
    {
        return '
					var rows = $(this).' . $this->getElementType() . '("getRoots");
					for (var i=0; i<rows.length; i++){
						$(this).' . $this->getElementType() . '("beginEdit", rows[i].' . $this->getWidget()->getUidColumn()->getDataColumnName() . ');
					}
				';
    }

    protected function addOnExpand($script)
    {
        $this->on_expand .= $script;
    }

    protected function buildJsOnExpandScript()
    {
        return $this->on_expand;
    }
    
    protected function buildJsOnBeforeLoadScript($js_var_param = 'param', $js_var_row = 'row')
    {
        return parent::buildJsOnBeforeLoadScript($js_var_param) . <<<JS
                    
                    // Make parentId a regular filter instead of an extra URL parameter
                    var parentId = {$js_var_param}['id'];
                    if (parentId) {
                        if ({$js_var_param}['data'] !== undefined && {$js_var_param}['data']['filters'] !== undefined && {$js_var_param}['data']['filters']['conditions'] !== undefined) {
                            var conditions = {$js_var_param}['data']['filters']['conditions'];
                            for (var c in conditions) {
                                if (conditions[c]['expression'] == '{$this->getWidget()->getTreeParentIdAttributeAlias()}') {
                                    {$js_var_param}['data']['filters']['conditions'][c]['value'] = row['{$this->getWidget()->getTreeFolderFilterColumn()->getDataColumnName()}'];
                                }
                            }
                        }
                        delete {$js_var_param}['id'];
                    } else {                        
                        {$this->buildJsOnBeforeLoadExapndFilters($js_var_param, $js_var_row)}
                    }

JS;
    }

    /**
     * Returns the JS code to add filters for 
     * @param string $js_var_param
     * @param string $js_var_row
     * @return string
     */
    protected function buildJsOnBeforeLoadExapndFilters($js_var_param = 'param', $js_var_row = 'row') : string
    {
        $widget = $this->getWidget();
        if (($widget->getKeepExpandedPathsOnRefresh() === false || $widget->isPaged() === false) && $widget->getLazyLoadTreeLevels() !== true) {
            return '';
        }
        $comparatorIn = ComparatorDataType::IN;
        $treeFolderFilterCol = $widget->getTreeFolderFilterColumn();
        $treeFolderFilterDelim = $treeFolderFilterCol->getAttribute()->getValueListDelimiter();
        return <<<JS

                    var treeData = $('#{$this->getId()}').{$this->getElementType()}('getData');
                        (function (){
                            function addNode(node) {
                                if ({$js_var_param}['data'] !== undefined && {$js_var_param}['data']['filters'] !== undefined && {$js_var_param}['data']['filters']['conditions'] !== undefined) {
                                    var conditions = {$js_var_param}['data']['filters']['conditions'];
                                    for (var c in conditions) {
                                        if (conditions[c]['expression'] == '{$this->getWidget()->getTreeParentIdAttributeAlias()}') {
                                            if (node['children'] !== undefined && node['state'] === 'open') {
                                                var oldValue = {$js_var_param}['data']['filters']['conditions'][c]['value'];
                                                {$js_var_param}['data']['filters']['conditions'][c]['value'] = oldValue + '{$treeFolderFilterDelim}' + node['{$treeFolderFilterCol->getDataColumnName()}'];
                                                {$js_var_param}['data']['filters']['conditions'][c]['comparator'] = '{$comparatorIn}';
                                            }
                                        }
                                    }
                                }
                                if (node['children'] !== undefined && node['state'] === 'open') {
                                    var children = node['children'];
                                    children.forEach(function (child) {
                                        if (child['children'] !== undefined && child['state'] === 'open') {
                                            addNode(child);
                                        }
                                    });
                                }
                                return null;
                            }                            
                            if (Array.isArray(treeData) && treeData.length > 0) {
                                if ({$js_var_param}['data'] !== undefined && {$js_var_param}['data']['filters'] !== undefined && {$js_var_param}['data']['filters']['conditions'] !== undefined) {
                                    var conditions = {$js_var_param}['data']['filters']['conditions'];
                                    for (var c in conditions) {
                                        if (conditions[c]['expression'] == '{$this->getWidget()->getTreeParentIdAttributeAlias()}') {
                                            var oldValue = {$js_var_param}['data']['filters']['conditions'][c]['value'];
                                            if (oldValue === '' || oldValue === undefined || oldValue === null) {
                                                {$js_var_param}['data']['filters']['conditions'][c]['value'] = '{$this->getWidget()->getTreeRootUid()}';
                                            }                                           
                                        }
                                    }
                                }
                                treeData.forEach(function (node) {
                                    if (node['children'] !== undefined && node['state'] === 'open') {
                                        addNode(node);
                                    }
                                });
                            }
                        })();
JS;
    }
    
    protected function buildJsOnBeforeLoadFunction()
    {
        if (! $this->buildJsOnBeforeLoadScript()) {
            return '';
        }
        
        return <<<JS
        
                function(row, param) {
    				{$this->buildJsOnBeforeLoadScript('param', 'row')}
				}
				
JS;
    }
    				
    public function buildJsDataGetter(ActionInterface $action = null)
    {
        $parentData = parent::buildJsDataGetter($action);
        // TODO #nested-data-sheets instead of removing children, replace the key by the alias of the relation to the child object (once nested sheets are supported)
        return <<<JS
function() {
    var data = {$parentData};
    for (var i in data.rows) {
        delete data.rows[i]['children'];
        delete data.rows[i]['state'];
    }
    return data;
}()
JS;
    }
}