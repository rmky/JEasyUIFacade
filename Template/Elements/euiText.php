<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Text;
use exface\Core\Templates\AbstractAjaxTemplate\Elements\JqueryAlignmentTrait;

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
        $text = nl2br($this->getWidget()->getText());
        $output = <<<HTML

        <{$this->getElementType()} class="exf-text {$this->buildCssElementClass()}" style="{$style}">{$text}</{$this->getElementType()}>

HTML;
        return $this->buildHtmlLabelWrapper($output);
    }

    public function generateJs()
    {
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
        return parent::getCaption() . ':';
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