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
        
        $value = $this->escapeString($this->getValueWithDefaults());
        $requiredScript = $widget->isRequired() ? 'required="true" ' : '';
        $disabledScript = $widget->isDisabled() ? 'disabled="disabled" ' : '';
        
        $output = <<<HTML

                    <input type="hidden"
						id="{$this->getId()}_value" 
						value="{$value}" />
                    <input class="easyui-{$this->getElementType()}" 
                        style="height: 100%; width: 100%;"
                        name="{$widget->getAttributeAlias()}"
                        value="{$value}"
                        id="{$this->getId()}"
                        {$requiredScript}
                        {$disabledScript}
                        data-options="{$this->buildJsDataOptions()}" />
HTML;
        
        return $this->buildHtmlWrapperDiv($output);
    }

    function generateJs()
    {
        $output = <<<JS

    {$this->buildJsDateParser()}
    {$this->buildJsDateFormatter()}
JS;
        
        return $output;
    }

    protected function buildJsDataOptions()
    {
        return <<<JS
formatter:{$this->getId()}_dateFormatter, parser:{$this->getId()}_dateParser
JS;
    }

    public function generateHeaders()
    {
        $headers = parent::generateHeaders();
        $headers[] = '<script type="text/javascript" src="exface/vendor/npm-asset/datejs/build/production/date.min.js"></script>';
        return $headers;
    }

    public function buildJsValueGetter()
    {
        $output = <<<JS
function(){ 
                    try {
                        return $('#{$this->getId()}').{$this->getElementType()}('getValue'); 
                    } catch (error) {
                        return $('#{$this->getId()}').val();
                    }
                }()
JS;
        
        return $output;
    }

    protected function buildJsDateFormat()
    {
        return 'yyyy-MM-dd';
    }

    protected function buildJsDateParser()
    {
        $output = <<<JS

    function {$this->getId()}_dateParser(s) {
        // funktionieren meist mit z.B. yyyy-MM-dd und yyyy-MM-dd HH:mm:ss.S
        // dd.MM.yyyy, dd-MM-yyyy, dd/MM/yyyy, d.M.yyyy, d-M-yyyy, d/M/yyyy
        var match = /(\d{1,2})[.\-/](\d{1,2})[.\-/](\d{4})/.exec(s);
        if (match != null) {
            return new Date(Number(match[3]), Number(match[2]) - 1, Number(match[1]));
        }
        // yyyy.MM.dd, yyyy-MM-dd, yyyy/MM/dd, yyyy.M.d, yyyy-M-d, yyyy/M/d
        var match = /(\d{4})[.\-/](\d{1,2})[.\-/](\d{1,2})/.exec(s);
        if (match != null) {
            return new Date(Number(match[1]), Number(match[2]) - 1, Number(match[3]));
        }
        // dd.MM.yy, dd-MM-yy, dd/MM/yy, d.M.yy, d-M-yy, d/M/yy
        var match = /(\d{1,2})[.\-/](\d{1,2})[.\-/](\d{2})/.exec(s);
        if (match != null) {
            return new Date(2000 + Number(match[3]), Number(match[2]) - 1, Number(match[1]));
        }
        // yy.MM.dd, yy-MM-dd, yy/MM/dd, yy.M.d, yy-M-d, yy/M/d
        var match = /(\d{2})[.\-/](\d{1,2})[.\-/](\d{1,2})/.exec(s);
        if (match != null) {
            return new Date(2000 + Number(match[1]), Number(match[2]) - 1, Number(match[3]));
        }
        // dd.MM, dd-MM, dd/MM, d.M, d-M, d/M
        var match = /(\d{1,2})[.\-/](\d{1,2})/.exec(s);
        if (match != null) {
            return new Date((new Date).getFullYear(), Number(match[2]) - 1, Number(match[1]));
        }
        // ddMMyyyy
        var match = /^(\d{2})(\d{2})(\d{4})$/.exec(s);
        if (match != null) {
            return new Date(Number(match[3]), Number(match[2]) - 1, Number(match[1]));
        }
        // ddMMyy
        var match = /^(\d{2})(\d{2})(\d{2})$/.exec(s);
        if (match != null) {
            return new Date(2000 + Number(match[3]), Number(match[2]) - 1, Number(match[1]));
        }
        // ddMM
        var match = /^(\d{2})(\d{2})$/.exec(s);
        if (match != null) {
            return new Date((new Date).getFullYear(), Number(match[2]) - 1, Number(match[1]));
        }
        // +/- ... T/D/W/M/J/Y
        var match = /^([+\-]?\d+)([TtDdWwMmJjYy])$/.exec(s);
        if (match != null) {
            var now = new Date();
            var dd = now.getDate();
            var MM = now.getMonth();
            var yyyy = now.getFullYear();
            switch (match[2].toUpperCase()) {
                case "T":
                case "D":
                    dd += Number(match[1]);
                    break;
                case "W":
                    dd += 7*Number(match[1]);
                    break;
                case "M":
                    MM += Number(match[1]);
                    break;
                case "J":
                case "Y":
                    yyyy += Number(match[1]);
            }
            return new Date(yyyy, MM, dd);
        }
        // TODAY, HEUTE, NOW, JETZT, YESTERDAY, GESTERN, TOMORROW, MORGEN
        switch (s.toUpperCase()) {
            case "TODAY":
            case "HEUTE":
            case "NOW":
            case "JETZT":
                var now = new Date();
                return new Date(now.getFullYear(), now.getMonth(), now.getDate());
                break;
            case "YESTERDAY":
            case "GESTERN":
                var now = new Date();
                return new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
                break;
            case "TOMORROW":
            case "MORGEN":
                var now = new Date();
                return new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
        }
        
        return null;
    }
JS;
        
        return $output;
    }

    protected function buildJsDateFormatter()
    {
        $output = <<<JS

    function {$this->getId()}_dateFormatter(date) {
        //return date.toString('{$this->buildJsDateFormat()}');
    }
JS;
        
        return $output;
    }
}