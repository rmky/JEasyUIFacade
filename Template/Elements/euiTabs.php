<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Tabs;

/**
 *
 * @author Andrej Kabachnik
 *        
 * @method Tabs getWidget()
 *        
 */
class euiTabs extends euiContainer
{

    public function generateHtml()
    {
        $output = <<<HTML
	<div id="{$this->getId()}" class="easyui-tabs" data-options="fit:true,border:false">
		{$this->buildHtmlForChildren()}
	</div>
HTML;
        return $output;
    }
    
    /*
     * public function generateJs(){
     * $js = parent::generateJs();
     *
     * foreach ($this->getWidget()->getTabs() as $nr => $tab){
     * if ($tab->isHidden()){
     * $js .= <<<JS
     *
     * $('#{$this->getId()}').tabs('close', {$nr});
     *
     * JS;
     * }
     * }
     * }
     */
}
?>