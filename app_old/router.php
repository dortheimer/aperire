<?php
class AperireRouter {

	static public function factory (){
		$router = new AperireRouter();

		$request_uri = explode('?',$_SERVER['REQUEST_URI']);
		$requestURI = explode('/',$request_uri[0]);

		$scriptName = explode('/',$_SERVER['SCRIPT_NAME']);

		for($i= 0;$i < sizeof($scriptName);$i++) {
			if ($requestURI[$i] == $scriptName[$i]){
				unset($requestURI[$i]);
			}
		}

		$command = array_values($requestURI);

		$router->controller	= !empty($command[0]) ? $command[0] : 'default';
		$router->action		= !empty($command[1]) ? $command[1] : 'default';
		$router->params 	= $_GET;
		$router->post 		= $_POST;

		return $router;

	}
}