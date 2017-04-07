<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\DataColumnTransposed;

class euiDataMatrix extends euiDataTable {
	private $label_values = array();
	
	protected function init(){
		parent::init();
		$this->set_element_type('datagrid');
		$this->build_js_transposer();
		
	}
	
	protected function build_js_transposer(){
		$visible_cols = array();
		$data_cols = array();
		$data_cols_totlas = array();
		$label_cols = array();
		foreach ($this->get_widget()->get_columns() as $col){
			if ($col instanceof DataColumnTransposed){
				$data_cols[] = $col->get_data_column_name();
				$label_cols[] = $col->get_label_attribute_alias();
				if ($col->get_footer()){
					$data_cols_totlas[$col->get_data_column_name()] = $col->get_footer();
				}
			} elseif (!$col->is_hidden()){
				$visible_cols[] = $col->get_data_column_name();
			}
		}
		$visible_cols= "'" . implode("','", $visible_cols) . "'";
		$data_cols= "'" . implode("','", $data_cols) . "'";
		$label_cols= "'" . implode("','", array_unique($label_cols)) . "'";
		$data_cols_totlas = json_encode($data_cols_totlas);
		
		$transpose_js = <<<JS
if (data.transposed) return data;

var dataCols = [ {$data_cols} ];
var dataColsTotals = {$data_cols_totlas};
var labelCols = [ {$label_cols} ];
var rows = data.rows;
var cols = $(this).datagrid('options').columns;
var colsNew = [];
var colsTransposed = {};
for (var i=0; i<cols.length; i++){
	var newColRow = [];
	for (var j=0; j<cols[i].length; j++){
		var fld = cols[i][j].field;
		if (dataCols.indexOf(fld) > -1){
			data.transposing = 1;
			colsTransposed[fld] = cols[i][j];
		} else if (labelCols.indexOf(fld) > -1) {
			// Add a subtitle column to show a caption for each subrow if there are multiple
			if (dataCols.length > 1){
				var newCol = $.extend(true, {}, cols[i][j]);
				newCol.field = fld+'_subtitle';
				newCol.title = '';
				newCol.align = 'right';
				newColRow.push(newCol);
			}
			// Create a column for each value if the label column
			var labels = [];
			for (var l=0; l<rows.length; l++){
				if (labels.indexOf(rows[l][fld]) == -1){
					labels.push(rows[l][fld]);
				}
			}
			for (var l=0; l<labels.length; l++){
				var newCol = $.extend(true, {}, cols[i][j]);
				newCol.field = rows[l][fld];
				newCol.title = '<span title="'+$(newCol.title).text()+' '+rows[l][fld]+'">'+rows[l][fld]+'</title>';
				// No header sorting if multiple sublines (not clear, what to sort!)
				if (dataCols.length > 1){
					newCol.sortable = false;
				}
				newColRow.push(newCol);
			}
			// Create a totals column if there are totals
			if (dataColsTotals !== {}){
				var totals = [];
				for (var t in dataColsTotals){
					var tfunc = dataColsTotals[t];
					if (totals.indexOf(tfunc) == -1){
						var newCol = $.extend(true, {}, cols[i][j]);
						newCol.field = fld+'_'+tfunc;
						newCol.title = tfunc;
						newCol.align = 'right';
						newColRow.push(newCol);
						totals.push(tfunc);
					}
				}
			}
		} else {
			newColRow.push(cols[i][j]);
		}
	}
	colsNew.push(newColRow);
}

if (data.transposing){
	var newRows = [];
	var newRowsObj = {};
	var visibleCols = [ {$visible_cols} ];
	for (var i=0; i<rows.length; i++){
		var newRowId = '';
		var newRow = {};
		var newColVals = {};
		var newColId = '';
		for (var fld in rows[i]){
			var val = rows[i][fld];
			if (labelCols.indexOf(fld) > -1){
				newColId = val;
				newColGroup = fld;
			} else if (dataCols.indexOf(fld) > -1){
				newColVals[fld] = val; 
			} else if (visibleCols.indexOf(fld) > -1) {
				newRowId += val;
				newRow[fld] = val;
			}
		}
		for (var fld in newColVals){
			if (newRowsObj[newRowId+fld] == undefined){
				newRowsObj[newRowId+fld] = $.extend(true, {}, newRow);
			}
			newRowsObj[newRowId+fld][newColId] = newColVals[fld];
			newRowsObj[newRowId+fld][newColGroup+'_subtitle'] = '<i>'+colsTransposed[fld].title+'</i>';
			if (dataColsTotals[fld] != undefined){
				var newVal = parseFloat(newColVals[fld]);
				var oldVal = newRowsObj[newRowId+fld][newColGroup+'_'+dataColsTotals[fld]];
				oldVal = oldVal ? oldVal : 0;
				switch (dataColsTotals[fld]){
					case 'SUM':
						newRowsObj[newRowId+fld][newColGroup+'_'+dataColsTotals[fld]] = oldVal + newVal; 
						break;
					case 'MAX':
						newRowsObj[newRowId+fld][newColGroup+'_'+dataColsTotals[fld]] = oldVal < newVal ? newVal : oldVal; 
						break;
					case 'MIN':
						newRowsObj[newRowId+fld][newColGroup+'_'+dataColsTotals[fld]] = oldVal > newVal ? newVal : oldVal; 
						break;
					case 'COUNT':
						newRowsObj[newRowId+fld][newColGroup+'_'+dataColsTotals[fld]] = oldVal + 1; 
						break;
					// TODO add more totals
				}
			}
		}
	}
	for (var i in newRowsObj){
		newRows.push(newRowsObj[i]);
	}
	data.rows = newRows;
	data.transposed = 1;
	data.transposing = 0;
	$(this).datagrid({columns: colsNew});
}
	

return data;
				
JS;
		$this->add_load_filter_script($transpose_js);
	}
	
}
?>