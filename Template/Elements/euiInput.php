<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Interfaces\Actions\ActionInterface;
use exface\Core\Widgets\Input;
use exface\AbstractAjaxTemplate\Template\Elements\JqueryLiveReferenceTrait;
use exface\Core\Factories\WidgetLinkFactory;

/**
 *
 * @method Input getWidget()
 *        
 * @author Andrej Kabachnik
 *        
 */
class euiInput extends euiAbstractElement
{
    
    use JqueryLiveReferenceTrait;

    protected function init()
    {
        parent::init();
        $this->setElementType('textbox');
        // If the input's value is bound to another element via an expression, we need to make sure, that other element will
        // change the input's value every time it changes itself. This needs to be done on init() to make sure, the other element
        // has not generated it's JS code yet!
        $this->registerLiveReferenceAtLinkedElement();
        
        // Register an onChange-Script on the element linked by a disable condition.
        $this->registerDisableConditionAtLinkedElement();
    }

    function generateHtml()
    {
        /* @var $widget \exface\Core\Widgets\Input */
        $widget = $this->getWidget();
        
        $output = '	<input style="height: 100%; width: 100%;"
						name="' . $widget->getAttributeAlias() . '" 
						value="' . $this->getValueWithDefaults() . '" 
						id="' . $this->getId() . '"  
						' . ($widget->isRequired() ? 'required="true" ' : '') . '
						' . ($widget->isDisabled() ? 'disabled="disabled" ' : '') . '
						/>
					';
        return $this->buildHtmlWrapperDiv($output);
    }

    public function getValueWithDefaults()
    {
        if ($this->getWidget()->getValueExpression() && $this->getWidget()->getValueExpression()->isReference()) {
            $value = '';
        } else {
            $value = $this->getWidget()->getValue();
        }
        if (is_null($value) || $value === '') {
            $value = $this->getWidget()->getDefaultValue();
        }
        return $this->escapeString($value);
    }

    protected function buildHtmlWrapperDiv($html)
    {
        if ($this->getWidget()->getCaption() && ! $this->getWidget()->getHideCaption()) {
            $input = '
						<label>' . $this->getWidget()->getCaption() . '</label>
						<div class="exf_input_wrapper">' . $html . '</div>';
        } else {
            $input = $html;
        }
        
        $output = '	<div class="fitem ' . $this->getMasonryItemClass() . ' exf_input" title="' . trim($this->buildHintText()) . '" style="width: ' . $this->getWidth() . '; min-width: ' . $this->getMinWidth() . '; height: ' . $this->getHeight() . ';">
						' . $input . '
					</div>';
        return $output;
    }

    function generateJs()
    {
        $output = '';
        $output .= "
				$('#" . $this->getId() . "')." . $this->getElementType() . "(" . ($this->buildJsDataOptions() ? '{' . $this->buildJsDataOptions() . '}' : '') . ");//.textbox('addClearBtn', 'icon-clear');
				";
        $output .= $this->buildJsLiveReference();
        $output .= $this->buildJsOnChangeHandler();
        
        // Initialize the disabled state of the widget if a disabled condition is set.
        $output .= $this->buildJsDisableConditionInitializer();
        
        return $output;
    }

    function buildJsInitOptions()
    {
        return $this->buildJsDataOptions();
    }

    protected function buildJsDataOptions()
    {
        return '';
    }

    function buildJsValueSetterMethod($value)
    {
        return $this->getElementType() . '("setValue", ' . $value . ')';
    }

    protected function buildJsOnChangeHandler()
    {
        if ($this->getOnChangeScript()) {
            return "$('#" . $this->getId() . "').change(function(event){" . $this->getOnChangeScript() . "});";
        } else {
            return '';
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::buildJsDataGetter($action, $custom_body_js)
     */
    public function buildJsDataGetter(ActionInterface $action = null)
    {
        if ($this->getWidget()->isDisplayOnly()) {
            return '{}';
        } else {
            return parent::buildJsDataGetter($action);
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::buildJsValidator()
     */
    function buildJsValidator()
    {
        $widget = $this->getWidget();
        
        $must_be_validated = ! ($widget->isHidden() || $widget->isReadonly() || $widget->isDisabled() || $widget->isDisplayOnly());
        if ($must_be_validated) {
            $output = '$("#' . $this->getId() . '").' . $this->getElementType() . '("isValid")';
        } else {
            $output = 'true';
        }
        
        return $output;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\JqueryLiveReferenceTrait::buildJsDisableCondition()
     */
    public function buildJsDisableCondition()
    {
        $output = '';
        if (($condition = $this->getWidget()->getDisableCondition()) && $condition->widget_link) {
            $link = WidgetLinkFactory::createFromAnything($this->getWorkbench(), $condition->widget_link);
            $link->setWidgetIdSpace($this->getWidget()->getIdSpace());
            $linked_element = $this->getTemplate()->getElementByWidgetId($link->getWidgetId(), $this->getPageId());
            if ($linked_element) {
                switch ($condition->comparator) {
                    case EXF_COMPARATOR_IS_NOT: // !=
                    case EXF_COMPARATOR_EQUALS: // ==
                    case EXF_COMPARATOR_EQUALS_NOT: // !==
                    case EXF_COMPARATOR_LESS_THAN: // <
                    case EXF_COMPARATOR_LESS_THAN_OR_EQUALS: // <=
                    case EXF_COMPARATOR_GREATER_THAN: // >
                    case EXF_COMPARATOR_GREATER_THAN_OR_EQUALS: // >=
                        $enable_widget_script = $this->getWidget()->isDisabled() ? '' : $this->buildJsEnabler() . ';
							// Sonst wird ein leeres required Widget nicht als invalide angezeigt
							$("#' . $this->getId() . '").' . $this->getElementType() . '("validate");';
                        
                        $output = <<<JS

						if ({$linked_element->buildJsValueGetter($link->getColumnId())} {$condition->comparator} "{$condition->value}") {
							{$this->buildJsDisabler()};
						} else {
							{$enable_widget_script}
						}
JS;
                        break;
                    case EXF_COMPARATOR_IN: // [
                    case EXF_COMPARATOR_NOT_IN: // ![
                    case EXF_COMPARATOR_IS: // =
                    default:
                    // TODO fuer diese Comparatoren muss noch der JavaScript generiert werden
                }
            }
        }
        return $output;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::buildJsEnabler()
     */
    function buildJsEnabler()
    {
        return '$("#' . $this->getId() . '").' . $this->getElementType() . '("enable")';
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see \exface\AbstractAjaxTemplate\Template\Elements\AbstractJqueryElement::buildJsDisabler()
     */
    function buildJsDisabler()
    {
        return '$("#' . $this->getId() . '").' . $this->getElementType() . '("disable")';
    }
}
?>