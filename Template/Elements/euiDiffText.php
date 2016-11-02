<?php
namespace exface\JEasyUiTemplate\Template\Elements;
use exface\Core\Widgets\DiffText;
class euiDiffText extends euiAbstractElement {
	
	public function generate_html(){
		$output = <<<HTML
				<div id="{$this->get_id()}_diffcontainer" class="difftext-container">
					<pre id="{$this->get_id()}_difforig" class="difftext-original" style="display: none;">
{$this->clean_text($this->get_widget()->get_left_value())}
					</pre>
					<pre id="{$this->get_id()}_diffnew" class="difftext-new" style="display: none;">
{$this->clean_text($this->get_widget()->get_right_value())}
					</pre>
					<pre id="{$this->get_id()}_diff" class="difftext-diff">
					</pre>
				</div>
HTML;
		return $output;
	}
	
	public function generate_js(){
		return '
				$("#' . $this->get_id() . '_diffcontainer").prettyTextDiff({
					cleanup: true,
					originalContainer: "#' . $this->get_id() . '_difforig",
					changedContainer: "#' . $this->get_id() . '_diffnew",
					diffContainer: "#' . $this->get_id() . '_diff"
				});
				';
	}
	
	protected function clean_text($string){
		return htmlspecialchars($string);
	}
	
	public function generate_headers(){
		return array (
			'<script type="text/javascript" src="exface/vendor/npm-asset/jquery-prettytextdiff/jquery.pretty-text-diff.min.js"></script>',
			'<script type="text/javascript" src="exface/vendor/npm-asset/diff-match-patch/index.js"></script>'
		);
	}
	
	/**
	 * @see \exface\JEasyUiTemplate\Template\Elements\euiAbstractElement::get_widget()
	 * @return DiffText
	 */
	public function get_widget(){
		return parent::get_widget();
	}
}
?>