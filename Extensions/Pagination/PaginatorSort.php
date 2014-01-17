<?php
namespace Elboletaire\Extensions\Pagination;

trait PaginatorSort {
	public static function paginate($perPage, $columns = array('*'), $orderBy = array())
	{
		if (empty($columns)) {
			$columns = array('*');
		}

		if (empty($orderBy)) {
			$orderBy = ['direction' => 'asc', 'sort' => 'id'];
		}

		if (!is_array($orderBy)) {
			list($sort, $direction) = explode(' ', $orderBy);
			$orderBy = compact('sort', 'direction');
		}

		foreach ($orderBy as $var => $default)
		{
			$orderBy[$var] = \Input::get($var, $default);
		}

		if (empty($orderBy['direction'])) {
			$orderBy['direction'] = 'asc';
		}

		return self::orderBy($orderBy['sort'], $orderBy['direction'])->paginate($perPage, $columns);
	}
}
