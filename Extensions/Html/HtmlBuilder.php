<?php
namespace Elboletaire\Extensions\Html;

use Illuminate\Html;

class HtmlBuilder extends Html\HtmlBuilder
{
	/**
	 * HTML tags layouts
	 *
	 * @var array
	 */
	protected $tags = [
		'a'           => '<a%2$s>%1$s</a>',
		'button'      => '<button%2$s>%1$s</button>',
		'style'       => '<link type="text/css" href="%1$s"%2$s />',
		'script'      => '<script src="%1$s"%2$s></script>',
		'scriptBlock' => '<script%2\$s>\n//<![CDATA[\n%1\$s\n//]]></script>'
	];

	/**
	 * {@inheritdoc}
	 */
	public function link($url, $title = null, $attributes = array(), $secure = null)
	{
		$url = $this->url->to($url, array(), $secure);
		return $this->tag('a', $title, array('href' => $url) + $attributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function linkRoute($name, $title = null, $parameters = array(), $attributes = array())
	{
		return $this->link($this->url->route($name, $parameters), $title, $attributes);
	}

	/**
	 * Generates an HTML tag based on the defined template in $this->tags
	 * @param  string $tagName   The tag template name
	 * @param  string $content    The content of the first defined var (%1$s) in the template
	 * @param  array  $attributes An optional list of attributes
	 * @return string
	 */
	public function tag($tagName, $content, $attributes = array())
	{
		if (!isset($this->tags[$tagName])) {
			trigger_error("Such tag (${tagName}) has not been implemented");
		}

		if (!isset($attributes['escape']) OR (isset($attributes['escape']) && $attributes['escape'])) {
			$content = $this->entities($content);
		}

		$tag = $this->tags[$tagName];

		return sprintf($tag, $content, $this->attributes($attributes));
	}
}
