<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\DataList;

/**
 * 
 * @method DataList getWidget()
 * 
 * @author Andrej Kabachnik
 *
 */
class euiDataList extends euiDataTable
{

    protected function init()
    {
        parent::init();
        $this->setElementType('datalist');
    }
    
}
?>