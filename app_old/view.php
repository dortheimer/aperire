<?php

class AperireView {

	public $header = false;
	public $footer = false;

	static public function factory ($conf = null){

		if (!@file_exists(BASE.'/app/view/'.$conf['view'].'.phtml')) {
			throw new AperireException("Page not found: View doesn't exist");
		}

		$view =  new AperireView();
		$view->viewPath = BASE.'/app/view/'.$conf['view'].'.phtml';
		if (file_exists(BASE.'/app/view/header.phtml')) {
			$view->header = BASE.'/app/view/header.phtml';
		}
		if (file_exists(BASE.'/app/view/footer.phtml')) {
			$view->footer = BASE.'/app/view/footer.phtml';
		}

		return $view;
	}

	public function set_data($data) {
		$this->data = $data;
	}

	public function render () {

		// Build local variables for view
		foreach ($this->data as $key => $val) {
			$$key = $val;
		}

		if (!empty($this->header)) {
			require $this->header;
		}

		require $this->viewPath;

		if (!empty($this->footer)) {
			require $this->footer;
		}

	}

}