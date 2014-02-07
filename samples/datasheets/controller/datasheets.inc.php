<?php

	class DatasheetsView extends View {

		function init() {
			//
		}
	}

	class DatasheetsController extends Controller {

		protected $view;

		function init() {
			$this->view = new DatasheetsView();
		}

		function indexAction() {
			global $site;
			// this allows chaining from Products
			$request = $site->mvc->getRequest();
			$data = isset($request->chain) ? array('product' => $request->chain->id) : array();
			// and this is the normal processing
			$this->view->render('datasheets/index-page', $data);
		}

		function showAction($id) {
			global $site;
			echo "<h1>Datasheet detail: {$id}</h1>";
			echo '<pre>' . print_r( $site->mvc->getRequest(), true ) . '</pre>';
		}

		function newAction() {
			global $site;
			echo "<h1>New datasheet</h1>";
			echo '<pre>' . print_r( $site->mvc->getRequest(), true ) . '</pre>';
		}

		function editAction($id) {
			global $site;
			echo "<h1>Edit datasheet: {$id}</h1>";
			echo '<pre>' . print_r( $site->mvc->getRequest(), true ) . '</pre>';
		}
	}

?>