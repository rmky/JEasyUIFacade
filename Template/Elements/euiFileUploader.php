<?php
namespace exface\JEasyUiTemplate\Template\Elements;
use exface\Core\Widgets\FileUploader;

class euiFileUploader extends euiAbstractElement {
	
	function init(){
		parent::init();
		$this->set_height_default(8);
	}
	
	function generate_html(){
		/* @var $widget \exface\Core\Widgets\FileUploader */
		$widget = $this->get_widget();
		$output = <<<HTML
<div id="{$this->get_id()}_pastearea" class="fitem exf_input" style="width:{$this->get_width()};height:{$this->get_height()};">
	<div class="easyui-panel" title="{$widget->get_caption()}" data-options="fit:true" style="padding:10px;">	
		<!-- The file input field used as target for the file upload widget -->
		<div style="float: left; width:100px;">
			<input id="{$this->get_id()}" type="file" name="files[]" multiple style="display: none;">
			<a href="javascript:;" class="easyui-linkbutton" onclick="$('#{$this->get_id()}').trigger('click');" data-options="iconCls: 'icon-add'" style="width:100px;">Add files</a>
		</div>
		<div style="width: calc(100% - 105px); margin: 2px 0 0 105px;">
			<div id="progress" class="easyui-progressbar"></div>
		</div>
		<!-- The container for the uploaded files -->
		<div id="{$this->get_id()}_files" class="fileupload-files"></div>
	</div>
</div>
HTML;
		return $output;
	}
	
	function generate_js(){
		$output = <<<JS
	$('#{$this->get_id()}_pastearea').pastableNonInputable();
	$('#{$this->get_id()}_pastearea').on('pasteImage', function(ev, data){
        $('#{$this->get_id()}').fileupload('add', {files: [data.blob]});
	  }).on('pasteText', function(ev, data){
        //$('<div class="result"></div>').text('text: "' + data.text + '"').insertAfter(this);
      });
    $('#{$this->get_id()}').fileupload({
        url: 'exface/vendor/exface/JEasyUiTemplate/Template/upload.php?sid={$this->get_template()->get_workbench()->context()->get_scope_window()->get_scope_id()}',
        dataType: 'json',
        autoUpload: true,
        {$this->generate_file_filter()}
        maxFileSize: {$this->get_widget()->get_max_file_size_bytes()},
        previewMaxWidth: 100,
        previewMaxHeight: 100,
        previewCrop: true
    }).on('fileuploadadd', function (e, data) {
    	console.log(data);
        data.context = $('<div class="fileupload-wrapper"/>').appendTo('#{$this->get_id()}_files');
        $.each(data.files, function (index, file) {
            var node = $('<div class="fileupload-inner"/>').append(
							$('<div class="fileupload-text">').append(
								$('<div class="fileupload-desc"/>').text('{$this->get_widget()->get_default_file_description()} ' + $('#{$this->get_id()}_files').children().length).append(
								$('<div class="fileupload-filename"/>').text(file.name))
							)
						);
            node.appendTo(data.context);
        });
    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index,
            file = data.files[index],
            node = $(data.context.children()[index]);
			pic = $('<div class="fileupload-preview"/>').prependTo(node);
		if (file.preview) {
            pic.append(file.preview);
        }
        if (file.error) {
            node.append($('<span class="text-danger"/>').text(file.error));
        }
    }).on('fileuploadprogressall', function (e, data) {
		$('#progress').progressbar('setValue', 0);
        var progress = parseInt(data.loaded / data.total * 100, 10);
		$('#progress').progressbar('setValue', progress);
    }).on('fileuploaddone', function (e, data) {
        $.each(data.result.files, function (index, file) {
			var node = $('<div class="fileupload-actions">').appendTo($(data.context.children()[index]).children('.fileupload-text'));
            if (file.url) {
                var link = $('<a>')
                    .attr('target', '_blank')
                    .prop('href', file.url)
					.text('View');
                node.append(link);
            } else if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                node.append(error);
            }
        });
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {
            var error = $('<span class="text-danger"/>').text('File upload failed.');
            $(data.context.children()[index])
                .append($('<div class="fileupload-text">').append(error));
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
JS;
		
		return $output;
	}
	
	function generate_headers(){
		$headers = array();
		// The jQuery UI widget factory, can be omitted if jQuery UI is already included -->
		$headers[] = '<script src="exface/vendor/bower-asset/blueimp-file-upload/js/vendor/jquery.ui.widget.js"></script>';
		// The Load Image plugin is included for the preview images and image resizing functionality -->
		$headers[] = '<script src="exface/vendor/npm-asset/blueimp-load-image/js/load-image.all.min.js"></script>';
		// The Iframe Transport is required for browsers without support for XHR file uploads -->
		$headers[] = '<script src="exface/vendor/bower-asset/blueimp-file-upload/js/jquery.iframe-transport.js"></script>';
		// The basic File Upload plugin -->
		$headers[] = '<script src="exface/vendor/bower-asset/blueimp-file-upload/js/jquery.fileupload.js"></script>';
		// The File Upload processing plugin -->
		$headers[] = '<script src="exface/vendor/bower-asset/blueimp-file-upload/js/jquery.fileupload-process.js"></script>';
		// The File Upload image preview & resize plugin -->
		$headers[] = '<script src="exface/vendor/bower-asset/blueimp-file-upload/js/jquery.fileupload-image.js"></script>';
		// The File Upload audio preview plugin -->
		$headers[] = '<script src="exface/vendor/bower-asset/blueimp-file-upload/js/jquery.fileupload-audio.js"></script>';
		// The File Upload video preview plugin -->
		$headers[] = '<script src="exface/vendor/bower-asset/blueimp-file-upload/js/jquery.fileupload-video.js"></script>';
		// The File Upload validation plugin -->
		$headers[] = '<script src="exface/vendor/bower-asset/blueimp-file-upload/js/jquery.fileupload-validate.js"></script>';
		$headers[] = '<script src="exface/vendor/bower-asset/paste.js/paste.js"></script>';
		return $headers;
	}
	
	/**
	 * @return FileUploader
	 * @see \exface\JEasyUiTemplate\Template\Elements\euiAbstractElement::get_widget()
	 */
	public function get_widget(){
		return parent::get_widget();
	}
	
	/**
	 * Generates the acceptedFileTypes option with a corresponding regular expressions if allowed_extensions is set
	 * for the widget
	 * @return string
	 */
	public function generate_file_filter(){
		if ($this->get_widget()->get_allowed_extensions()){
			return 'acceptFileTypes: /(\.|\/)(' . str_replace(array(',', ' '), array('|', ''), $this->get_widget()->get_allowed_extensions()) . ')$/i,';
		} else {
			return '';
		}
	}
}
?>