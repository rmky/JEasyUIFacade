<?php
namespace exface\JEasyUiTemplate\Template\Elements;

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
