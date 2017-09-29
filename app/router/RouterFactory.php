<?php

namespace App;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


class RouterFactory
{
	use Nette\StaticClass;

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;

		// admin routes
		$router[] = new Route('admin/<presenter>/<action>[/<id>]', [
			'module' => 'Admin',
			'presenter' => 'Articles',
			'action' => 'default'
		]);

		// front routes
		$router[] = new Route('api/<action>[/<id \d+>]', [
			'module' => 'Front',
			'presenter' => 'Api',
			'action' => 'default',
			'articleId' => null
		]);
		$router[] = new Route('clanek/<articleUrl>', 'Front:ArticleDetail:default');
		$router[] = new Route('[strana<paginator-page=1>]', [
			'module' => 'Front',
			'presenter' => 'Homepage',
			'action' => 'default',
			'do' => 'paginator-showPage'
		]);

		return $router;
	}
}
