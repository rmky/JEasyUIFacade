<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\InputNumber;

/**
 * @method InputNumber getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class euiInputNumber extends euiInput
{

    protected function init()
    {
        parent::init();
        $this->setElementType('numberbox');
    }

    protected function buildJsDataOptions()
    {
        $widget = $this->getWidget();
        $output = parent::buildJsDataOptions();
        $output = ($output ? ',' : '') . $output;
        
        $formatter = $this->buildJsFormatter('value');
        
        $output .= "precision: " . ($widget->getPrecisionMax() ? $widget->getPrecisionMax() : 10)
                . ", decimalSeparator: '{$widget->getDecimalSeparator()}'"
                . ($formatter ?  ", formatter:function(value){" . $formatter . "}" : "")
				;
        return trim($output, ',');
    }
    
    protected function buildJsFormatter($value_js_var)
    {
        $widget = $this->getWidget();
        if (is_null($widget->getPrecisionMax()) || $widget->getPrecisionMin() === $widget->getPrecisionMax()){
            $js = <<<JS
            
            if ({$value_js_var} !== undefined && {$value_js_var} !== ''){
    			var {$value_js_var} = $.fn.numberbox.defaults.formatter.call(this,{$value_js_var});
                {$value_js_var} = {$value_js_var}.replace(/{$widget->getDecimalSeparator()}/g, '.');
    			var number = parseFloat({$value_js_var});
                {$value_js_var} = number.toString().replace(/\./g, '{$widget->getDecimalSeparator()}');
    			return {$value_js_var};
            }

JS;
        }
        return $js;
    }
}