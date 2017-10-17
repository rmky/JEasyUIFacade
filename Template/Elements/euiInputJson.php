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
        $output = '<div id="' . $this->getId() . '" style="height: 100%; width: 100%;"></div>';
        return $this->buildHtmlWrapperDiv($output);
    }

    function generateJs()
    {
        $init_value = $this->getValueWithDefaults() ? $this->getId() . '_JSONeditor.set(' . $this->getWidget()->getValue() . ');' : '';
        $script = <<<JS
            var {$this->getId()}_JSONeditor = new JSONEditor(document.getElementById("{$this->getId()}"), {
                            					mode: 'tree',
                           						modes: ['code', 'form', 'text', 'tree', 'view']
                        					});
            {$init_value}
            {$this->getId()}_JSONeditor.expandAll();
            $('#{$this->getId()}').parents('.exf_input').children('label').css('vertical-align', 'top');
JS;
        return $script;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::buildJsValueGetter()
     */
    public function buildJsValueGetter()
    {
        return 'function(){var text = ' . $this->getId() . '_JSONeditor.getText(); if (text === "{}" || text === "[]") { return ""; } else { return text;}}';
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
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::buildJsValidator()
     */
    function buildJsValidator()
    {
        return 'true';
    }
}