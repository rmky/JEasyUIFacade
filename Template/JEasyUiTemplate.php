<?php

namespace exface\JEasyUiTemplate\Template;

use exface\AbstractAjaxTemplate\Template\AbstractAjaxTemplate;
use exface\Core\Exceptions\DependencyNotFoundError;

class JEasyUiTemplate extends AbstractAjaxTemplate
{

    public function init()
    {
        parent::init();
        $this->setClassPrefix('eui');
        $this->setClassNamespace(__NAMESPACE__);
        $this->setRequestSystemVars(array(
            '_'
        ));
        $folder = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'jeasyui';
        if (! is_dir($folder)) {
            throw new DependencyNotFoundError('jEasyUI files not found! Please install jEasyUI to "' . $folder . '"!', '6T6HUFO');
        }
    }

    public function getRequestFilters()
    {
        parent::getRequestFilters();
        // id is a special filter for dynamic tree loading in jeasyui
        if ($this->getWorkbench()->getRequestParams()['id']) {
            $this->request_filters_array['PARENT'][] = urldecode($this->getWorkbench()->getRequestParams()['id']);
            $this->getWorkbench()->removeRequestParam('id');
        }
        return $this->request_filters_array;
    }
}
?>