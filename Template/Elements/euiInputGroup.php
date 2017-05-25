<?php

namespace exface\JEasyUiTemplate\Template\Elements;

class euiInputGroup extends euiPanel
{

    public function generateHtml()
    {
        $children_html = $this->buildHtmlForChildren();
        
        // Wrap children widgets with a grid for masonry layouting - but only if there is something to be layed out
        if ($this->getWidget()->countWidgets() > 1) {
            $children_html = '<div class="grid">' . $children_html . '</div>';
        }
        
        $output = '
				<fieldset class="exface_inputgroup 
						id="' . $this->getId() . '" 
						data-options="' . $this->buildJsDataOptions() . '">
					<legend>' . $this->getWidget()->getCaption() . '</legend>
					' . $children_html . '
				</fieldset>';
        return $output;
    }
}
?>