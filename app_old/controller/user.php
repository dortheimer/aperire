<?php
require_once BASE.'/app/controller/login.php';;

class AperireControllerUser extends AperireControllerLogin
{

	public function defaultAction($error = '') {

	}

	public function signupAction($error='') {

		$this->view = AperireView::factory(array(
				'view'=>'register'
		));

		$data = new stdClass();
		$data->headline = 'Sign up';
		$data->error 	= $error;
		$data->back_url = !empty(Aperire::$Router->params['back_url']) ? Aperire::$Router->params['back_url'] : '/';
		$this->view->set_data($data);
		$this->view->render();
	}
	protected function initModel () {

	}

	protected function initView () {

	}

	public function createAction(){

		$back_url = !empty(Aperire::$Router->post['back_url']) ? Aperire::$Router->post['back_url'] : '/';
		try{
			//Create user
			AperireModelUser::create(Aperire::$Router->post);

			// Login
			$this->logonAction(array(
				'email'		=> Aperire::$Router->post['email'],
				'password'	=> Aperire::$Router->post['password']
			));

			header('Location: '.$back_url);
			exit;
		}
		catch (AperireException $e){
			$this->view = AperireView::factory(array(
					'view'=>'login'
			));
			$this->signupAction($e->getMessage());
			exit;
		}
		catch (Exception $e){
			$this->view = AperireView::factory(array(
					'view'=>'login'
			));
			$this->signupAction($e->getMessage());
			exit;
		}
	}
}