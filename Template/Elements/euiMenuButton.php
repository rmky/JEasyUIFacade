<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\AbstractAjaxTemplate\Template\Elements\JqueryButtonTrait;
use exface\Core\Widgets\Dialog;

/**
 * generates jEasyUI-Buttons for ExFace
 *
 * @author Andrej Kabachnik
 *        
 */
class euiMenuButton extends euiAbstractElement
{
    
    use JqueryButtonTrait;

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::init()
     */
    protected function init()
    {
        parent::init();
        $this->setElementType('menubutton');
    }

    /**
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\abstractWidget::generateHtml()
     */
    function generateHtml()
    {
        $buttons_html = '';
        $output = '';
        
        foreach ($this->getWidget()->getButtons() as $b) {
            // If the button has an action, make some action specific HTML depending on the action
            if ($action = $b->getAction()) {
                if ($action->implementsInterface('iShowDialog')) {
                    $dialog_widget = $action->getDialogWidget();
                    $output .= $this->getTemplate()->generateHtml($dialog_widget);
                }
            }
            // In any case, create a menu entry
            $icon = $b->getIconName() ? ' iconCls="' . $this->buildCssIconClass($b->getIconName()) . '"' : '';
            $disabled = $b->isDisabled() ? ' disabled=true' : '';
            $buttons_html .= '<div' . $icon . $disabled . '>
					' . $b->getCaption() . '
				</div>
				';
        }
        
        $icon = $this->getWidget()->getIconName() ? ',iconCls:\'' . $this->buildCssIconClass($this->getWidget()->getIconName()) . '\'' : '';
        switch ($this->getWidget()->getAlign()) {
            case EXF_ALIGN_LEFT:
                $align_style = 'float: left;';
                break;
            case EXF_ALIGN_RIGHT:
                $align_style = 'float: right;';
                break;
            default:
                $align_style = '';
        }
        $output .= '<a href="javascript:void(0)" id="' . $this->getId() . '" class="easyui-' . $this->getElementType() . '" data-options="menu:\'#' . $this->buildJsMenuName() . '\'' . $icon . '" style="' . $align_style . '">
				' . $this->getWidget()->getCaption() . '
			</a>
			<div id="' . $this->buildJsMenuName() . '">
				' . $buttons_html . '
			</div>
			';
        
        if ($this->getWidget()->getInputWidget() instanceof Dialog && ! $this->getWidget()->getParent() instanceof Dialog) {
            // Hier wird versucht zu unterscheiden wo sich der Knopf befindet. Der Wrapper wird nur benoetigt
            // wenn er sich in einem Dialog befindet, aber nicht als Knopf im Footer, sondern im Inhalt.
            $output = $this->buildHtmlWrapperDiv($output);
        }
        
        return $output;
    }

    /**
     */
    function buildHtmlButton()
    {}

    /**
     *
     * @param unknown $html            
     * @return string
     */
    protected function buildHtmlWrapperDiv($html)
    {
        $output = '<div class="fitem ' . $this->getMasonryItemClass() . ' exf_input" title="' . trim($this->buildHintText()) . '" style="width: ' . $this->getWidth() . '; height: ' . $this->getHeight() . ';">
				' . $html . '
			</div>
			';
        return $output;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::generateJs()
     */
    function generateJs()
    {
        $output = '';
        $output .= '$("#' . $this->buildJsMenuName() . '").menu({
				onClick:function(item){
					switch(item.text) {
						';
        
        foreach ($this->getWidget()->getButtons() as $b) {
            $output .= 'case "' . $b->getCaption() . '":
							' . $this->getTemplate()->getElement($b)->buildJsClickFunction() . '
							break;
						';
        }
        $output .= '}
				}
			});';
        return $output;
    }

    /**
     *
     * @return string
     */
    function buildJsMenuName()
    {
        return $this->getId() . '_menu';
    }
}
?>