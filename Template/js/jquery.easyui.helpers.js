function jeasyui_create_dialog(parentElement, id, options, content, parseContent){
	parseContent = parseContent ? true : false;
	var dialog = $('<div class="easyui-dialog" id="'+id+'"></div>');
	parentElement.append(dialog);
	$.parser.parse(content);
	dialog.append(content);
	if (parseContent){
		$.parser.parse(dialog);
	}
	dialog.dialog(options);
}

/*$.extend($.fn.textbox.methods, {
	addClearBtn: function(jq, iconCls){
		return jq.each(function(){
			var t = $(this);
			var opts = t.textbox('options');
			opts.icons = opts.icons || [];
			opts.icons.unshift({
				iconCls: iconCls,
				handler: function(e){
					$(e.data.target).textbox('clear').textbox('textbox').focus();
					$(this).css('visibility','hidden');
				}
			});
			t.textbox();
			if (!t.textbox('getText')){
				t.textbox('getIcon',0).css('visibility','hidden');
			}
			t.textbox('textbox').bind('keyup', function(){
				var icon = t.textbox('getIcon',0);
				if ($(this).val()){
					icon.css('visibility','visible');
				} else {
					icon.css('visibility','hidden');
				}
			});
		});
	}
});*/