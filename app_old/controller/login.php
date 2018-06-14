<?php

class AperireControllerLogin extends AperireController
{

	public function defaultAction($error = '') {
		$this->model = AperireModel::factory(array(
				'model'=>'user'
		));
		$this->view = AperireView::factory(array(
				'view'=>'login'
		));

		$data = new stdClass();
		$data->headline = 'Sign in';
		$data->error 	= $error;
		$data->back_url = !empty(Aperire::$Router->params['back_url']) ? Aperire::$Router->params['back_url'] : '/';

		$this->view->set_data($data);
		$this->view->render();
	}

	public function logonAction($conf=array()) {

		$email 		= !empty($conf['email']) ?
						$conf['email'] :
						(empty(Aperire::$Router->post['email']) ? '' : Aperire::$Router->post['email']);

		$password 	= !empty($conf['password']) ?
						$conf['password'] :
						(empty(Aperire::$Router->post['password']) ? '' : Aperire::$Router->post['password']);

		$back_url 	= !empty(Aperire::$Router->post['back_url']) ? Aperire::$Router->post['back_url'] : '/';
		$this->model = AperireModel::factory(array(
				'model'=>'user'
		));

		try {
			if (!$email) {
				throw new AperireException('no email');
			}
			if (!$password) {
				throw new AperireException('no password');
			}
			$this->model->login($email, $password);
			header('Location:'.$back_url);
			exit;
		}
		catch (AperireException $e){
			$error = $e->getMessage();
print_r($error);
			$this->defaultAction($error);
		}
		catch (Exception $e){
			print_R($e);
		}
	}
	protected function initModel () {

	}

	protected function initView () {

	}
}