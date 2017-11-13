<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Text;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryAlignmentTrait;
use exface\Core\Interfaces\DataTypes\EnumDataTypeInterface;
use exface\Core\DataTypes\NumberDataType;
use exface\Core\DataTypes\DateDataType;
use exface\Core\DataTypes\TimestampDataType;
use exface\Core\DataTypes\TextStylesDataType;
use exface\Core\Interfaces\Widgets\iTakeInput;

/**
 * @method Text getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class euiText extends euiAbstractElement
{

    use JqueryAlignmentTrait;
    
    protected function init()
    {
        parent::init();
        $this->setElementType($this->getCaption() ? 'span' : 'p');
        return;
    }
    
    public function generateHtml()
    {
        $widget = $this->getWidget();
        
        $style = 'text-align: ' . $this->buildCssTextAlignValue($widget->getAlign(), EXF_ALIGN_LEFT);
        $text = nl2br(str_replace(' 00:00:00', '', $this->getWidget()->getText()));
        
        if ($widget->getAttribute()) {
            switch ($widget->getStyle()) {
                case TextStylesDataType::BOLD:
                    $style .= "font-weight: bold;";
                    break;
                case TextStylesDataType::ITALIC:
                    $style .= "font-style: italic;";
                    break;
                case TextStylesDataType::UNDERLINE:
                    $style .= "text-decoration: underline;";
                    break;
                case TextStylesDataType::UNDERLINE:
                    $style .= "text-decoration: line-through;";
                    break;
            }
        }
        
        $output = <<<HTML

        <{$this->getElementType()} id="{$this->getId()}" class="exf-text {$this->buildCssElementClass()}" style="{$style}">{$text}</{$this->getElementType()}>

HTML;
        return $this->buildHtmlLabelWrapper($output);
    }

    public function generateJs()
    {
        $js = '';
        
        if ($formatter = $this->buildJsValueFormatter('val')) {
            $js = <<<JS
    $('#{$this->getId()}').text({$formatter}());
JS;
        }
            
        return $js;     
    }
    
    protected function buildJsValueFormatter() 
    {
        $type = $this->getWidget()->getDataType();
        $js_var_value = 'val';
        switch (true) {
            case $type instanceof EnumDataTypeInterface :
                $js_value_labels = json_encode($type->getLabels());
                $formatter = <<<JS
                
        var labels = {$js_value_labels};
        return labels[{$js_var_value}] ? labels[{$js_var_value}] : {$js_var_value};
        
JS;
                break;
            case $type instanceof NumberDataType :
                $translator = $this->getWorkbench()->getCoreApp()->getTranslator();
                $decimal_separator = $translator->translate('LOCALIZATION.NUMBER.DECIMAL_SEPARATOR');
                $thousands_separator = $type->getGroupDigits() ? $translator->translate('LOCALIZATION.NUMBER.THOUSANDS_SEPARATOR') : '';
                $locale = $this->getWorkbench()->context()->getScopeSession()->getSessionLocale();
                $formatter = euiInputNumber::buildJsNumberFormatter($js_var_value, $type->getPrecisionMin(), $type->getPrecisionMax(), $decimal_separator, $thousands_separator, $locale);
                break;
            case $type instanceof DateDataType:
            case $type instanceof TimestampDataType:
                $format = $type instanceof TimestampDataType ? $this->translate("DATETIME.FORMAT.SCREEN") : $this->translate("DATE.FORMAT.SCREEN");
                $formatter = <<<JS
        if (! {$js_var_value}) {
            return {$js_var_value};
        }
        return Date.parse({$js_var_value}).toString("{$format}");
        
JS;
                break;
        }
        
        // Formatter option
        if ($formatter) {
            return <<<JS
function(){
    var val = $('#{$this->getId()}').text(); 
    try {
        {$formatter}
    } catch (e) {
        return {$js_var_value};
    } 
}

JS;
        }

        return '';
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \exface\Core\Templates\AbstractAjaxTemplate\Elements\AbstractJqueryElement::buildCssHeightDefaultValue()
     */
    protected function buildCssHeightDefaultValue()
    {
        return 'auto';
    }
    
    protected function getCaption()
    {
        $caption = parent::getCaption();
        return  $caption . ($caption && ! $this->getWidget() instanceof iTakeInput ?  ':' : '');
    }
    
    protected function buildHtmlLabelWrapper($html)
    {
        if ($caption = $this->getCaption()) {
            $input = '
						<label>' . $caption . '</label>
						<div class="exf-labeled-item">' . $html . '</div>';
        } else {
            $input = $html;
        }
        
        $output = '	<div class="exf-grid-item ' . $this->getMasonryItemClass() . ' exf-input" title="' . trim($this->buildHintText()) . '" style="width: ' . $this->getWidth() . '; min-width: ' . $this->getMinWidth() . '; height: ' . $this->getHeight() . ';">
						' . $input . '
					</div>';
        return $output;
    }
}
?>