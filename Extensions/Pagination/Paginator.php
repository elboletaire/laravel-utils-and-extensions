<?php 
namespace Elboletaire\Extensions\Pagination;

use Illuminate\Pagination;

class Paginator extends Pagination\Paginator
{
	protected $params = array();

	public function __construct(Pagination\Environment $env, array $items, $total, $perPage)
	{
		$this->fillParams();

		parent::__construct($env, $items, $total, $perPage);
	}

	public function sort($column, $title = null, $attributes = array())
	{
		if (empty($title)) {
			$title = ucfirst($column);
		}

		$params = array(
			'sort'      => $column,
			'direction' => 'asc'
		);

		if ($this->params['sort'] == $column) {
			$params['direction'] = $this->params['direction'] == 'asc' ? 'desc' : 'asc';
			$attributes['class'] = $this->getDirectionClass($attributes, $params['direction']);
		}

		$this->appends($params);
		$link = app('html')->link($this->getUrl($this->getCurrentPage()), $title, $attributes);
		$this->removes(array('sort', 'direction'));

		return $link;
	}

	public function removeQuery($name)
	{
		unset($this->query[$name]);

		return $this;
	}

	public function removes($queryVars = array())
	{
		if (!is_array($queryVars)) {
			$queryVars = array($queryVars);
		}

		foreach ($queryVars as $var) {
			$this->removeQuery($var);
		}
	}

	private function getDirectionClass(&$attributes, $direction)
	{
		$direction = $direction == 'desc' ? 'asc' : 'desc';
		return empty($attributes['class']) ? $direction : $attributes['class'] . ' ' . $direction;
	}

	private function fillParams()
	{
		$params = array('page' => 1, 'sort' => null, 'direction' => null);

		foreach ($params as $param => $default)
		{
			$this->params[$param] = \Input::get($param, $default);
		}
	}

	public function links()
	{
		$this->appends(array_diff_key($this->params, ['page' => null]));
		return parent::links();
	}
}
