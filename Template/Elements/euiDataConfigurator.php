<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\DataConfigurator;

/**
 * 
 * @method DataConfigurator getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class euiDataConfigurator extends euiTabs
{    
    /**
     * Returns the default number of columns to layout this widget.
     *
     * @return integer
     */
    public function getDefaultColumnNumber()
    {
        return $this->getTemplate()->getConfig()->getOption("WIDGET.DATACONFIGURATOR.COLUMNS_BY_DEFAULT");
    }
}
?>
