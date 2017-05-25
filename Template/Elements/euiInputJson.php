<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputJson extends euiInputText
{

    protected function init()
    {
        parent::init();
        $this->setElementType('div');
        $this->setHeightDefault(5);
    }

    function generateHtml()
    {
        $output = ' <input type="hidden"
							name="' . $this->getWidget()->getAttributeAlias() . '"
							id="' . $this->getId() . '">
					<div id="' . $this->getId() . '_editor" style="height: 100%; width: 100%;"></div>';
        return $this->buildHtmlWrapperDiv($output);
    }

    function generateJs()
    {
        $init_value = $this->getWidget()->getValue() ? 'editor.set(' . $this->getWidget()->getValue() . ');' : '';
        $script = <<<JS
	var container = document.getElementById("{$this->getId()}_editor");
    var editor = new JSONEditor(container, 
    				{
    					mode: 'tree',
   						modes: ['code', 'form', 'text', 'tree', 'view'],
   						change: function(){ $('#{$this->getId()}').val(editor.getText()); }
					}
    	);
    {$init_value}
    editor.expandAll();
    $(container).parents('.exf_input').children('label').css('vertical-align', 'top');
	$('#{$this->getId()}').val(editor.getText());
JS;
        return $script;
    }

    public function generateHeaders()
    {
        $includes = parent::generateHeaders();
        $includes[] = '<link href="exface/vendor/bower-asset/jsoneditor/dist/jsoneditor.min.css" rel="stylesheet">';
        $includes[] = '<script type="text/javascript" src="exface/vendor/bower-asset/jsoneditor/dist/jsoneditor.min.js"></script>';
        return $includes;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::buildJsValidator()
     */
    function buildJsValidator()
    {
        return 'true';
    }
}