<?php
namespace exface\JEasyUiTemplate\Template\Elements;

use exface\Core\Widgets\DiffText;

class euiDiffText extends euiAbstractElement
{

    public function generateHtml()
    {
        $output = <<<HTML
				<div id="{$this->getId()}_diffcontainer" class="difftext-container">
					<pre id="{$this->getId()}_difforig" class="difftext-original" style="display: none;">
{$this->cleanText($this->getWidget()->getLeftValue())}
					</pre>
					<pre id="{$this->getId()}_diffnew" class="difftext-new" style="display: none;">
{$this->cleanText($this->getWidget()->getRightValue())}
					</pre>
					<pre id="{$this->getId()}_diff" class="difftext-diff">
					</pre>
				</div>
HTML;
        return $output;
    }

    public function generateJs()
    {
        return '
				$("#' . $this->getId() . '_diffcontainer").prettyTextDiff({
					cleanup: true,
					originalContainer: "#' . $this->getId() . '_difforig",
					changedContainer: "#' . $this->getId() . '_diffnew",
					diffContainer: "#' . $this->getId() . '_diff"
				});
				';
    }

    protected function cleanText($string)
    {
        return htmlspecialchars($string);
    }

    public function generateHeaders()
    {
        return array(
            '<script type="text/javascript" src="' . $this->getTemplate()->getUrlOfVendorFolder() . '/npm-asset/jquery-prettytextdiff/jquery.pretty-text-diff.min.js"></script>',
            '<script type="text/javascript" src="' . $this->getTemplate()->getUrlOfVendorFolder() . '/bower-asset/google-diff-match-patch-js/diff_match_patch.js"></script>'
        );
    }

    /**
     *
     * @see \exface\JEasyUiTemplate\Template\Elements\euiAbstractElement::getWidget()
     * @return DiffText
     */
    public function getWidget()
    {
        return parent::getWidget();
    }
}
?>