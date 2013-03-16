<?php

use Nette\Application\Routers\RouteList,
	Nette\Application\Routers\Route,
	Nette\Application\Routers\SimpleRouter;


/**
 * Router factory.
 */
class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public function createRouter()
	{
		$router = new RouteList();
		$router[] = new Route('index.php', 'Present:list', Route::ONE_WAY);
		$router[] = new Route('clean-cache', 'Present:cleanCache');
		$router[] = new Route('[<presentation>/]', 'Present:list');
		return $router;
	}

}
