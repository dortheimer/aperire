<?php
/**
* Aperire
*
* Copyright 2013 Jonathan Dortheimer
* Released under the MIT license
*
* Date: 14/3/2013
*/

/**
* Bootstrapper
*
*
*/

class AperireBootstrap {

	static public $config;
	/**
	 * Boot
	 * Calls function that load stuff
	 */
	static public function boot () {
		self::loadContants();
		self::loadConfig();
		self::loadIncludes();
		self::initDb();
		self::loadMVC();
		self::initSession();
	}

	static public function initDb () {

		Aperire::$db = Zend_Db::factory(Aperire::$config->db->adapter, array(
			'host'     => Aperire::$config->db->host,
		    'username' => Aperire::$config->db->username,
		    'password' => Aperire::$config->db->password,
		    'dbname'   => Aperire::$config->db->dbname
		));
		Aperire::$db->query('SET NAMES UTF8');
	}
	static public function loadMVC () {

		include BASE.'/app/model.php';
		include BASE.'/app/view.php';
		include BASE.'/app/controller.php';
	}

	static public function initSession() {
		include BASE.'/app/model/user.php';

		Zend_Session::start();
		Aperire::$session = new Zend_Session_Namespace('Aperire');
		if (Aperire::$session->user){
			Aperire::$user = Aperire::$session->user;
		}
		else {
			Aperire::$user = AperireModel::factory(array(
				'model'=>'user'
		));
		}
	}

	static public function loadIncludes () {
		require_once 'Zend/db/adapter/'.str_replace('_','/',Aperire::$config->db->adapter)	.'.php';
		require_once 'Zend/registry.php';
		require_once 'Zend/db/table.php';

		require_once 'Zend/Session.php';

		include BASE.'/app/exception.php';
		include BASE.'/app/router.php';
	}

	static public function loadContants () {
		define("BASE", substr(__DIR__,0,-strlen(basename(__DIR__))));
	}

	static public function loadConfig () {
		include BASE.'/app/aperire.php';
		include ('Zend/Config/ini.php');
		Aperire::$config = new Zend_Config_Ini(BASE.'/app/config.ini', 'development');
	}

	static public function run () {
		// Save router in application registry;
		Aperire::$Router = AperireRouter::factory();
		$controller = AperireController::factory(Aperire::$Router);
		$controller->dispatch();
	}
}