<?php
namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputDate extends euiInput
{

    protected function init()
    {
        parent::init();
        $this->setElementType('datebox');
    }

    function generateHtml()
    {
        /* @var $widget \exface\Core\Widgets\Input */
        $widget = $this->getWidget();
        $output = '	<input class="easyui-' . $this->getElementType() . '" 
						style="height: 100%; width: 100%;"
						name="' . $widget->getAttributeAlias() . '"
						value="' . $this->escapeString($this->getValueWithDefaults()) . '"
						id="' . $this->getId() . '"
						' . ($widget->isRequired() ? 'required="true" ' : '') . '
						' . ($widget->isDisabled() ? 'disabled="disabled" ' : '') . '
						data-options="' . $this->buildJsDataOptions() . '" />
					';
        return $this->buildHtmlWrapperDiv($output);
    }

    function generateJs()
    {
        return '';
    }

    protected function buildJsDataOptions()
    {
        return 'formatter:function(date){return date.toString(\'' . $this->buildJsDateFormat() . '\');}, parser:function(s){return Date.parse(s);}';
    }

    public function generateHeaders()
    {
        $headers = parent::generateHeaders();
        $headers[] = '<script type="text/javascript" src="exface/vendor/npm-asset/datejs/build/production/date.min.js"></script>';
        return $headers;
    }

    public function buildJsValueGetter()
    {
        return "function(){ try {return $('#" . $this->getId() . "')." . $this->getElementType() . "('getValue'); } catch (error) {return '';} }()";
    }

    protected function buildJsDateFormat()
    {
        return 'yyyy-MM-dd';
    }
}