<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\AbstractAjaxTemplate\Template\Elements\JqueryButtonGroupTrait;

/**
 * The jEasyUI implementation of the ButtonGroup widget
 * 
 * @author Andrej Kabachnik
 *        
 * @method ButtonGroup getWidget()
 */
class euiButtonGroup extends euiAbstractElement
{
    use JqueryButtonGroupTrait;
    
    protected function getMoreButtonsMenuCaption(){
        return '...';
    }
}
?>