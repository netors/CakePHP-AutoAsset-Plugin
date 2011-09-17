<?php
/**
 * AssetLoader Helper
 *
 * Assists in outputting prerequisite (required) and asynchronous (lazy-loaded)
 *  CSS and JavaScript assets with the help of the HtmlHelper.
 */
class AssetLoaderHelper extends AppHelper {
	/**
	 * View helpers required by this helper
	 */
	public $helpers = array('Html');

	/**
	 * Indicates whether or not the required (prerequisite) files
	 *  have already been output;
	 */
	var $requiredDone = false;

	/**
	 * Returns a string containing the HTML output for the required Javascript
	 *  and CSS files referenced within $assets
	 */
	public function required(&$assets) {
		$output = '';

		if ($this->requiredDone) {
			return $output;
		}

		if (isset($assets['css']['required'])) {
			if (is_string($assets['css']['required'])) {
				$assets['css']['required'] = array($assets['css']['required']);
			}

			foreach ($assets['css']['required'] as $asset) {
				if (!empty($output)) {
					$output .= "\n";
				}

				$output .= $this->Html->css($asset, null, array('inline' => true));
			}
		}

		if (!empty($output)) {
			$output .= "\n";
		}

		if (isset($assets['js']['required'])) {
			if (is_string($assets['js']['required'])) {
				$assets['js']['required'] = array($assets['js']['required']);
			}

			foreach ($assets['js']['required'] as $asset) {
				if (!empty($output)) {
					$output .= "\n";
				}

				$output .= $this->Html->script($asset, array('inline' => true));
			}
		}

		$this->requiredDone = true;

		return $output;
	}

	public function globals(&$assets) {
		$output = '';

		if (!isset($assets['globals']) || !is_array($assets['globals'])) {
			return $output;
		}

		foreach ($assets['globals'] as $key => $value) {
			if (!empty($output)) {
				$output .= "\n";
			}

			$output .= 'var '.$key.' = '.$value.';';
		}

		$output = $this->Html->scriptBlock($output, array('inline' => true));

		return $output;
	}

	/**
	 * Returns the HTML output to lazy-load the configured Javascript and Css
	 *
	 * Also includes the required CSS and JS if it has not already been output with the
	 *  required($assets) function, since they need to appear before the async assets.
	 */
	public function load(&$assets) {
		$output = '';

		$output .= '$css.path = \'/css/\';'."\n";
		$output .= '$script.path = \'/js/\';';

		if (isset($assets['css']['async']) && (!empty($assets['css']['async']))) {
			foreach ($assets['css']['async'] as $asset) {
				if (!empty($output)) {
					$output .= "\n";
				}

				$o = (is_array($asset))
					? '[\''.implode('\', \'', $asset).'\']'
					: '\''.$asset.'\'';

				$output .= '$css('.$o.');';
			}
		}

		if (!empty($output)) {
			$output .= "\n";
		}

		if (isset($assets['js']['async']) && (!empty($assets['js']['async']))) {
			if (!empty($output)) {
				$output .= "\n";
			}

			foreach ($assets['js']['async'] as $asset) {
				if (!empty($output)) {
					$output .= "\n";
				}

				$o = (is_array($asset))
					? '[\''.implode('\', \'', $asset).'\']'
					: '\''.$asset.'\'';

				$output .= '$script('.$o.');';
			}
		}

		$output = $this->Html->scriptBlock($output, array('inline' => true));

		if (!$this->requiredDone) {
			$output = $this->required($assets) . "\n\n" . $output;
			$this->requiredDone = true;
		}

		return $output;
	}
}
?>