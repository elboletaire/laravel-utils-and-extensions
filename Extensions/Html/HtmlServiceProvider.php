<?php
namespace Elboletaire\Extensions\Html;

use Illuminate\Html;

class HtmlServiceProvider extends Html\HtmlServiceProvider
{
	public function boot()
	{
		$this->app->bind('html', function($app) {
			return new HtmlBuilder($app['url']);
		});

		$this->app->bind('form', function($app) {
			$form = new FormBuilder($app['html'], $app['url'], $app['session.store']->getToken());

			return $form->setSessionStore($app['session.store']);
		});
	}
}
