<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\Image;

/**
 *
 * @method Image get_widget()
 *        
 * @author Andrej Kabachnik
 *        
 */
class euiImage extends euiText
{

    function generateHtml()
    {
        $style = '';
        if (! $this->getWidget()->getWidth()->isUndefined()) {
            $width = ' width="' . $this->getWidth() . '"';
        }
        if (! $this->getWidget()->getHeight()->isUndefined()) {
            $height = ' height="' . $this->getHeight() . '"';
        }
        
        switch ($this->getWidget()->getAlign()) {
            case EXF_ALIGN_CENTER:
                $style .= 'margin-left: auto; margin-right: auto;';
                break;
            case EXF_ALIGN_RIGHT:
                $style .= 'float: right';
        }
        
        $output = '<img src="' . $this->getWidget()->getUri() . '"' . $width . $height . ' style="' . $style . '" />';
        return $output;
    }

    function generateJs()
    {
        return '';
    }
}
?>