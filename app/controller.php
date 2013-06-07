<?php

class AperireController {

	static public function factory ($router = null){

		$controllerClassName = 'AperireController'.$router->controller;
		if (!@require_once BASE.'app/controller/'.$router->controller.'.php') {
			throw new AperireException("Page not found: Controller doesn't exist");
		}
		return new $controllerClassName();
	}

	public function dispatch () {
		$action = Aperire::$Router->action.'Action';
		if (!method_exists($this,$action)){
			throw new AperireException('Controller '.$action.' action doesn\'t exist');
		}
		$this->initModel();
		$this->initView();

		$this->$action();
	}

	protected function initModel () {
		throw new AperireException ('Controller must implement the initModel method');
	}

	protected function initView () {
		throw new AperireException ('Controller must implement the initView method');
	}

}