<?php

class AperireControllerProjects extends AperireController {

	protected function initModel () {

	}

	protected function initView () {

	}

	protected function process () {

	}
	public function defaultAction() {
		AperireModel::factory(array(
			'model'=>'project'
		));
		$this->view = AperireView::factory(array(
				'view'=>'projects'
		));
		$data = new stdClass();
		$data->projects = AperireModelProject::getProjects();
		$data->headline 		= 'Projects';
		$data->description 	= 'This is the current list of open projects';

		$this->view->set_data($data);
		$this->view->render();
	}

	protected function toolAction () {
		$this->model = AperireModel::factory(array(
				'model'=>'project',
				'id'=>Aperire::$Router->params['id']
		));
		$this->view = AperireView::factory(array(
				'view'=>'tool'
		));

		$data = new stdClass();
		$data = $this->pupulate_nav($data);
		$data->headline 		= 'New policy tool';
		$data->post_url			= $this->model->url();
		$this->view->set_data($data);
		$this->view->render();
	}

	protected function pupulate_nav($data){
		$data->rate_url		= $this->model->url('rate');
		$data->new_tool_url	= $this->model->url('tool');
		$data->contributors_url = $this->model->url();
		$data->packages_url = $this->model->url('packages');
		$data->userid		= Aperire::$user->id;
		$data->login_url	= '/login/?back_url='.urlencode($_SERVER["REQUEST_URI"]);
		return $data;
	}

	protected function viewAction () {
		$this->model = AperireModel::factory(array(
				'model'=>'project',
				'id'=>Aperire::$Router->params['id']
		));
		$this->view = AperireView::factory(array(
				'view'=>'contributors'
		));
		$data = new stdClass();

		$data = $this->pupulate_nav($data);
		$data->id		 	= $this->model->getId();
		$data->headline 	= $this->model->getName();
		$data->description 	= $this->model->getDescription();
		$data->contributors = $this->model->getContributors();
		$this->view->set_data($data);
		$this->view->render();
	}
	protected function packagesAction () {

		$this->model = AperireModel::factory(array(
				'model'=>'project',
				'id'=>Aperire::$Router->params['id']
		));
		$this->view = AperireView::factory(array(
				'view'=>'project'
		));

		// Add new tool
		if (!empty(Aperire::$Router->post['new_tool'])){
			// Save new tool
			$this->model->create_tool(Aperire::$Router->post['new_tool']);
		}

		$data = new stdClass();

		$data = $this->pupulate_nav($data);
		$data->id		 	= $this->model->getId();
		$data->headline 	= $this->model->getName();
		$data->description 	= $this->model->getDescription();
		$data->policies 	= $this->model->getTools();

		$this->view->set_data($data);
		$this->view->render();
	}

	protected function rateAction () {

		$this->model = AperireModel::factory(array(
				'model'=>'project',
				'id'=>Aperire::$Router->params['id']
		));

		// add new rating
		if (sizeof(Aperire::$Router->post)>0){
			// Save answers
			foreach (Aperire::$Router->post as $key => $val){
				list($test,$rel_kind,$tool_id, $tool_id_rel) = explode('_',$key);
				if ($test == 'question'){
					$this->model->create_relation($rel_kind, $tool_id, $tool_id_rel,$val);
				}
			}
		}

		$data = new stdClass();
		$data = $this->pupulate_nav($data);

		$data->headline 		= $this->model->getName();
		$data->description 		= $this->model->getDescription();
		$data->project_url		= $this->model->url();
		$data->questions		= $this->model->getRandomTools();
		$data->score			= $this->model->score();
		$data->post_url			= $this->model->url('rate');
		$this->view = AperireView::factory(array(
				'view'=>'rate_kind'.$data->questions[0]->kind
		));
		$this->view->set_data($data);
		$this->view->render();
	}
}