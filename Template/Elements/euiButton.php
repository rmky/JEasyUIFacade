<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\DialogButton;
use exface\Core\Interfaces\Actions\ActionInterface;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryButtonTrait;
use exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement;
use exface\Core\Widgets\Dialog;

/**
 * generates jEasyUI-Buttons for ExFace
 *
 * @author Andrej Kabachnik
 *        
 */
class euiButton extends euiAbstractElement
{
    
    use JqueryButtonTrait;

    function generateJs()
    {
        $output = '';
        $action = $this->getAction();
        
        // Generate helper functions, that do not depend on the action
        
        // Get the click function for the button. This might also be required for buttons without actions
        if ($click = $this->buildJsClickFunction()) {
            // Generate the function to be called, when the button is clicked
            $output .= "
				function " . $this->buildJsClickFunctionName() . "(){
					" . $click . "
				}";
        }
        
        // Get the java script required for the action itself
        if ($action) {
            // Actions with template scripts may contain some helper functions or global variables.
            // Print the here first.
            if ($action && $action->implementsInterface('iRunTemplateScript')) {
                $output .= $this->getAction()->printHelperFunctions();
            }
        }
        
        return $output;
    }

    /**
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\abstractWidget::generateHtml()
     */
    function generateHtml()
    {
        // Create a linkbutton
        $output .= $this->buildHtmlButton();
        
        return $output;
    }

    public function buildHtmlButton()
    {
        $widget = $this->getWidget();
        
        $style = '';
        switch ($widget->getAlign()) {
            case EXF_ALIGN_LEFT:
                $style .= 'float: left;';
                break;
            case EXF_ALIGN_RIGHT:
                $style .= 'float: right;';
                break;
        }
        
        $data_options = '';
        if ($widget->getVisibility() != EXF_WIDGET_VISIBILITY_PROMOTED) {
            $data_options .= 'plain: true';
        } else {
            $data_options .= 'plain: false';
        }
        if ($widget->isDisabled()) {
            $data_options .= ', disabled: true';
        }
        if ($widget->getIconName() && !$widget->getHideButtonIcon()) {
            $data_options .= ", iconCls: '" . $this->buildCssIconClass($widget->getIconName()) . "'";
        }
        
        $output = '
				<a id="' . $this->getId() . '" title="' . str_replace('"', '\"', $widget->getHint()) . '" href="#" class="easyui-linkbutton" data-options="' . $data_options . '" style="' . $style . '" onclick="' . $this->buildJsFunctionPrefix() . 'click();">
						' . $widget->getCaption() . '
				</a>';
        return $output;
    }

    protected function buildJsClickShowDialog(ActionInterface $action, AbstractJqueryElement $input_element)
    {
        $widget = $this->getWidget();
        
        /* @var $prefill_link \exface\Core\CommonLogic\WidgetLink */
        $prefill = '';
        if ($prefill_link = $this->getAction()->getPrefillWithDataFromWidgetLink()) {
            if ($prefill_link->getPageId() == $widget->getPageId()) {
                $prefill = ", prefill: " . $this->getTemplate()->getElement($prefill_link->getWidget())->buildJsDataGetter($this->getAction());
            }
        }
        
        $output = $this->buildJsRequestDataCollector($action, $input_element);
        $output .= <<<JS
						{$this->buildJsBusyIconShow()}
						$.ajax({
							type: 'POST',
							url: '{$this->getAjaxUrl()}',
							dataType: 'html',
							data: {
								action: '{$widget->getActionAlias()}',
								resource: '{$widget->getPageId()}',
								element: '{$widget->getId()}',
								data: requestData
								{$prefill}
							},
							success: function(data, textStatus, jqXHR) {
								{$this->buildJsCloseDialog($widget, $input_element)}
		                       	if ($('#ajax-dialogs').length < 1){
		                       		$('body').append('<div id=\"ajax-dialogs\"></div>');
                       			}
								$('#ajax-dialogs').append('<div class=\"ajax-wrapper\">'+data+'</div>');
								var dialogId = $('#ajax-dialogs').children().last().children('.easyui-dialog').attr('id');
		                       	$.parser.parse($('#ajax-dialogs').children().last());
								var onCloseFunc = $('#'+dialogId).panel('options').onClose;
								$('#'+dialogId).panel('options').onClose = function(){
									onCloseFunc();
									
									// MenuButtons manuell zerstoeren, um Ueberbleibsel im body zu verhindern
									var menubuttons = $('#'+dialogId).parent().find('.easyui-menubutton');
									for (i = 0; i < menubuttons.length; i++) {
										$(menubuttons[i]).menubutton('destroy');
									}
									
									$(this).dialog('destroy').remove(); 
									$('#ajax-dialogs').children().last().remove();
									{$this->buildJsInputRefresh($widget, $input_element)}
								};
                       			$(document).trigger('{$action->getAliasWithNamespace()}.action.performed', [requestData]);
                       			{$this->buildJsBusyIconHide()}
							},
							error: function(jqXHR, textStatus, errorThrown){
								{$this->buildJsShowError('jqXHR.responseText', 'jqXHR.status + " " + jqXHR.statusText')}
								{$this->buildJsBusyIconHide()}
							}
						});
						{$this->buildJsCloseDialog($widget, $input_element)} 
JS;
        return $output;
    }

    protected function buildJsCloseDialog($widget, $input_element)
    {
        if ($widget instanceof DialogButton && $widget->getCloseDialogAfterActionSucceeds()){
            if ($widget->getInputWidget() instanceof Dialog){
                return "$('#" . $input_element->getId() . "').dialog('close');";
            } else {
                // IDEA close the closest parent dialog here? This maybe usefull
                // if the dialog button has a custom input widget - not a dialog.
            }
        }
        return "";
    }

    /**
     * In jEasyUI the button does not need any extra headers, as all headers needed for whatever the button loads will
     * come with the AJAX-request.
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::generateHeaders()
     */
    public function generateHeaders()
    {
        return array();
    }
}
?>