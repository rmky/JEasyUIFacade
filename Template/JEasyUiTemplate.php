<?php
namespace exface\JEasyUiTemplate\Template;

use exface\Core\Templates\AbstractAjaxTemplate\AbstractAjaxTemplate;
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
    
    protected function generateTemplateHeaders()
    {
        return [
            '<link rel="stylesheet" type="text/css" href="' . $this->getUrlOfVendorFolder() . '/exface/JEasyUiTemplate/Template/js/jeasyui/themes/metro-blue/easyui.css">',
            '<link rel="stylesheet" type="text/css" href="' . $this->getUrlOfVendorFolder() . '/exface/JEasyUiTemplate/Template/js/template.css">',
            '<script type="text/javascript" src="' . $this->getUrlOfVendorFolder() . '/bower-asset/jquery/dist/jquery.min.js"></script>',
            '<script type="text/javascript" src="' . $this->getUrlOfVendorFolder() . '/bower-asset/jeasyui/jquery.easyui.min.js"></script>',
            '<script type="text/javascript" src="' . $this->getUrlOfVendorFolder() . '/bower-asset/jeasyui/locale/easyui-lang-de.js"></script>',
            '<script type="text/javascript" src="' . $this->getUrlOfVendorFolder() . '/exface/JEasyUiTemplate/Template/js/jquery.easyui.patch.1.43.js"></script>',
            '<script type="text/javascript" src="' . $this->getUrlOfVendorFolder() . '/exface/JEasyUiTemplate/Template/js/template.js"></script>',
            '<link href="' . $this->getUrlOfVendorFolder() . '/bower-asset/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />'
        ];
    }
}
?>