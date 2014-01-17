<?php
namespace Elboletaire\Utils;

use Elboletaire\Extensions\Html\HtmlBuilder;

/**
 * Utility for easy lessc usage under Laravel 4
 *
 * @author Òscar Casajuana <elboletaire@underave.net>
 * @version 1.3
 */

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

class Less
{
	/**
	 * Default lesscss options.
	 *
	 * @var array
	 */
	public $less_options = array(
		'env'      => 'production'
	);

	/**
	 * The resulting options array from merging default and user values (on setOptions)
	 *
	 * @var array
	 */
	private $options = array();

	/**
	 * Stores the compilation error, in case it occurs
	 *
	 * @var boolean
	 */
	public $error = false;

	/**
	 * The lessphp compiler instance
	 *
	 * @var lessc
	 */
	private $Lessc;

	/**
	 * The HtmlBuilder instance
	 *
	 * @var [type]
	 */
	private $Html;

	/**
	 * The css path name, where the output files will be stored
	 *
	 * @var string
	 */
	private $css_path  = 'css';

	/**
	 * The lesscss path name, where all original .less files reside
	 *
	 * @var string
	 */
	private $less_path = 'less';

	/**
	 * Initializes Lessc and cleans less and css paths
	 */
	public function __construct()
	{
		$this->Lessc = new \lessc();

		$this->less_path = trim($this->less_path, '/');
		$this->css_path  = trim($this->css_path, '/');

		$this->Html = new HtmlBuilder;
	}

	/**
	 * Compile the less and return a css <link> tag.
	 * In case of error, it will load less with  javascript
	 * instead of returning the resulting css <link> tag.
	 *
	 * @param  string $less The input .less file to be compiled
	 * @param  array  $options An array of options to be passed as a json to the less javascript object.
	 * @return string The resulting <link> tag for the compiled css, or the <link> tag for the .less & less.min if compilation fails
	 * @throws Exception
	 */
	public function less($less = 'styles.less', array $options = array())
	{
		$options = $this->setOptions($less, $options);

		$css = $options['output'];
		$this->cleanOptions($options);

		if ($options['env'] == 'development')
		{
			return $this->jsBlock($less, $options);
		}

		try
		{
			$this->compile($less, $css);
			return $this->Html->tag('style', asset("{$this->css_path}/${css}"), array('rel' => 'stylesheet'));
		}
		catch (Exception $e)
		{
			$this->error = $e->getMessage();
			Log::error("Error compiling less file: " . $this->error);
			// maybe here we should also add a trigger_error, but less.js should treat the error as we're gonna load it now
			return $this->jsBlock($less, $options);
		}
	}

	/**
	 * Returns the initialization string for less (javascript based)
	 *
	 * @param  string $less The input .less file to be loaded
	 * @param  array  $options An array of options to be passed to the `less` configuration var
	 * @return string The link + script tags need to launch lesscss
	 */
	public function jsBlock($less, array $options = array())
	{
		$options = $this->setOptions($less, $options);

		$lessjs = $options['less'];
		$this->cleanOptions($options);

		$return = '';
		// Append the user less file
		$return .= $this->Html->tag('style', asset('/' . $this->less_path . '/' . $less), array('rel' => 'stylesheet/less'));
		// Less.js configuration
		$return .= $this->Html->tag('scriptBlock', sprintf('less = %s;', json_encode($options)));
		// <script> tag for less.js file
		$return .= $this->Html->tag('script', $lessjs);

		return $return;
	}

	/**
	 * Compiles an input less file to an output css file using the PHP compiler
	 *
	 * @param  string $input The input .less file to be compiled
	 * @param  string $output The output .css file, resulting from the compilation
	 * @return boolean true on success, false otherwise
	 */
	public function compile($input, $output)
	{
		// load cache file
		$cache_file = app('path.storage') . DS . 'cache' . DS . basename($input) . '.cache';
		$input      = app('path.public') . DS . $this->less_path . DS . basename($input);
		$output     = app('path.public') . DS . $this->css_path  . DS . basename($output);

		if (file_exists($cache_file)) {
			$cache = unserialize(file_get_contents($cache_file));
		} else {
			$cache = $input;
		}

		$this->Lessc->setImportDir(array(dirname($input), app('path.public') . DS . $this->less_path . DS . 'bootstrap'));

		$new_cache = $this->Lessc->cachedCompile($cache);

		if (empty($new_cache) || empty($new_cache['compiled'])) {
			throw new Exception("Error compiling less file");
		}

		if (!is_array($cache) || $new_cache['updated'] > $cache['updated']) {
			if (false === file_put_contents($cache_file, serialize($new_cache))) {
				throw new Exception("Could not write less cache file to $cache_file");
			}
			if (false === file_put_contents($output, $new_cache['compiled'])) {
				throw new Exception("Could not write output css file to $output");
			}
			return true;
		}

		if (isset($cache['compiled']) && file_exists($cache_file) && !file_exists($output)) {
			if (false === file_put_contents($output, $new_cache['compiled'])) {
				throw new Exception("Could not write output css file to $output");
			}
		}

		return false;
	}

	/**
	 * Sets the less configuration var options based on the ones given by the user
	 * and our default ones.
	 *
	 * @param string $less The input .less file to be processed
	 * @param array  $options An array of options to be passed to the javascript less configuration var
	 * @return array $options The resulting $options array
	 */
	private function setOptions($less, array $options)
	{
		if (!empty($this->options)) {
			return $this->options;
		}

		$options = array_merge($this->less_options, $options);

		$options['rootpath'] = asset('/');

		if (empty($options['output'])) {
			$pathinfo = pathinfo($less);
			$options['output'] = $pathinfo['filename'] . '.css';
		}

		if (empty($options['less'])) {
			$options['less'] = asset('/js/less.min');
		}

		return $this->options = $options;
	}

	/**
	 * Removes undesired keys from $options var
	 *
	 * @param  array  $options The $options var
	 * @return void
	 */
	private function cleanOptions(array &$options)
	{
		unset($options['output'], $options['less']);
	}
}
