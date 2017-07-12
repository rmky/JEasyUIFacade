<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Menu;

/**
 * 
 * @method Menu getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class euiMenu extends euiAbstractElement 
{
    /**
     * 
     * {@inheritDoc}
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::generateHtml()
     */
    public function generateHtml()
    {
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
        
        return <<<HTML
<div id="{$this->getId()}" class="easyui-menu" style="{$align_style}">
    {$this->buildHtmlButtons()}
</div>
HTML;
    }
    
    /**
     * Renders buttons as <div> elements
     * 
     * @return string
     */
    public function buildHtmlButtons()
    {
        $buttons_html = '';
        $last_parent = null;
        foreach ($this->getWidget()->getButtons() as $b) {
            // Insert separators between button groups (neighbouring buttons with
            // different parents.
            if (!is_null($last_parent) && $last_parent != $b->getParent()){
                $buttons_html .= '<div class="menu-sep"></div>';
            }
            $last_parent = $b->getParent();
            
            // Create a menu entry
            $icon = $b->getIconName() ? ' iconCls="' . $this->buildCssIconClass($b->getIconName()) . '"' : '';
            $disabled = $b->isDisabled() ? ' disabled=true' : '';
            $buttons_html .= <<<HTML
                <div {$icon} {$disabled} id="{$this->getTemplate()->getElement($b)->getId()}" onclick="{$this->getTemplate()->getElement($b)->buildJsClickFunctionName()}()">
    				{$b->getCaption()}
    			</div>
HTML;
        }
        return $buttons_html;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::generateJs()
     */
    public function generateJs()
    {
        $buttons_js = '';
        foreach ($this->getWidget()->getButtons() as $btn){
            $buttons_js .= $this->getTemplate()->getElement($btn)->generateJs();
        }
        return $buttons_js;
    }
}