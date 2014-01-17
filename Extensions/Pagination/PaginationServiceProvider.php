<?php
namespace Elboletaire\Extensions\Pagination;

use Illuminate\Pagination;

class PaginationServiceProvider extends Pagination\PaginationServiceProvider
{
	public function boot()
	{
		$this->app->bind('paginator', function($app) {
			$paginator = new Environment($app['request'], $app['view'], $app['translator']);

			$paginator->setViewName($app['config']['view.pagination']);

			return $paginator;
		});
	}
}
