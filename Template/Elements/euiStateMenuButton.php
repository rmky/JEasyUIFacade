<?php
namespace exface\JEasyUiTemplate\Template\Elements;

/**
 *
 * @author SFL
 *        
 */
class euiStateMenuButton extends euiMenuButton
{

    /**
     *
     * @see \exface\Templates\jeasyui\Widgets\abstractWidget::generateHtml()
     */
    function generateHtml()
    {
        $widget = $this->getWidget();
        $button_no = count($widget->getButtons());
        $output = '';
        
        if ($button_no == 1) {
            /* @var $b \exface\Core\Widgets\Button */
            $b = $widget->getButtons()[0];
            $b->setCaption($widget->getCaption());
            $b->setAlign($widget->getAlign());
            $b->setVisibility($widget->getVisibility());
            $output = $this->getTemplate()->getElement($b)->generateHtml();
        } elseif ($button_no > 1) {
            $output = parent::generateHtml();
        }
        
        return $output;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\euiMenuButton::generateJs()
     */
    function generateJs()
    {
        $widget = $this->getWidget();
        $button_no = count($widget->getButtons());
        $output = '';
        
        if ($button_no == 1) {
            $output = $this->getTemplate()->getElement($widget->getButtons()[0])->generateJs();
        } elseif ($button_no > 1) {
            $output = parent::generateJs();
        }
        
        return $output;
    }
}
?>
