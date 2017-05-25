<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\SplitPanel;

/**
 *
 * @method SplitPanel getWidget()
 * @author aka
 *        
 */
class euiSplitPanel extends euiPanel
{

    private $region = null;

    function generateHtml()
    {
        switch ($this->getRegion()) {
            case 'north':
            case 'south':
                $height = $this->getHeight();
                break;
            case 'east':
            case 'west':
                $width = $this->getWidth();
                break;
            case 'center':
                $height = $this->getHeight();
                $width = $this->getWidth();
                break;
        }
        
        if ($height && ! $this->getWidget()->getHeight()->isPercentual()) {
            $height = 'calc( ' . $height . ' + 7px)';
        }
        if ($width && ! $this->getWidget()->getWidth()->isPercentual()) {
            $width = 'calc( ' . $width . ' + 7px)';
        }
        
        $style = ($height ? 'height: ' . $height . ';' : '') . ($width ? 'width: ' . $width . ';' : '');
        
        $children_html = $this->buildHtmlForChildren();
        
        // Wrap children widgets with a grid for masonry layouting - but only if there is something to be layed out
        if ($this->getWidget()->countWidgets() > 1) {
            $children_html = '<div class="grid">' . $children_html . '</div>';
        }
        
        $output = '
				<div id="' . $this->getId() . '" data-options="' . $this->buildJsDataOptions() . '"' . ($style ? ' style="' . $style . '"' : '') . '>
					' . $children_html . '
				</div>
				';
        return $output;
    }

    public function buildJsDataOptions()
    {
        /* @var $widget \exface\Core\Widgets\LayoutPanel */
        $widget = $this->getWidget();
        $output = parent::buildJsDataOptions();
        
        $output .= ($output ? ',' : '') . 'region:\'' . $this->getRegion() . '\'
					,title:\'' . $widget->getCaption() . '\'' . ($this->getRegion() !== 'center' ? ',split:' . ($widget->getResizable() ? 'true' : 'false') : '');
        
        return $output;
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function setRegion($value)
    {
        $this->region = $value;
        return $this;
    }
}
?>