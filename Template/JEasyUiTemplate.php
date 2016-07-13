<?php namespace exface\JEasyUiTemplate\Template;

use exface\AbstractAjaxTemplate\Template\AbstractAjaxTemplate;
use exface\Core\Exceptions\TemplateError;

class JEasyUiTemplate extends AbstractAjaxTemplate {
	
	public function init(){
		$this->set_class_prefix('eui');
		$this->set_class_namespace(__NAMESPACE__);
		$this->set_request_system_vars(array('_'));
		$folder = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'jeasyui';
		if (!is_dir($folder)){
			throw new TemplateError('jEasyUI files not found! Please install jEasyUI to "' . $folder . '"!');
		}
	}
	
	public function get_request_filters(){
		parent::get_request_filters();
		// id is a special filter for dynamic tree loading in jeasyui
		if ($this->exface()->get_request_params()['id']){
			$this->request_filters_array['PARENT'] = urldecode($this->exface()->get_request_params()['id']);
			$this->exface()->remove_request_param('id');
		}
		return $this->request_filters_array;
	}
}
?>