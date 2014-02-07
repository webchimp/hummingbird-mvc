<?php

	class ProductsView extends View {

		function init() {
			//
		}
	}

	class ProductsController extends Controller {

		protected $view;

		function init() {
			$this->view = new ProductsView();
			// allow chaining to Datasheets
			$this->addChainedController('Datasheets');
			$this->addChainedController('Datasheets', 'hojas-de-datos');
		}

		function indexAction() {
			global $site;
			$this->view->render('products/index-page');
		}

		function showAction($id) {
			global $site;
			$request = $site->mvc->getRequest();
			echo "<h1>Product detail: {$id}</h1>";
			echo '<pre>' . print_r( $site->mvc->getRequest(), true ) . '</pre>';
		}

		function nuevoAction() {
			$this->newAction();
		}

		function newAction() {
			global $site;
			echo "<h1>New product</h1>";
			echo '<pre>' . print_r( $site->mvc->getRequest(), true ) . '</pre>';
		}

		function editAction($id) {
			global $site;
			echo "<h1>Edit product: {$id}</h1>";
			echo '<pre>' . print_r( $site->mvc->getRequest(), true ) . '</pre>';
		}
		function jsonAction() {
			global $site;
			$response = $site->mvc->getResponse();
			$request = $site->mvc->getRequest();
			$response->setHeader('Content-Type', 'application/json');
			$response->write( json_encode( array('limit' => $request->param('limit', 15), 'foo' => 'bar', 'bar' => 'baz') ) );
		}

		function errorAction() {
			global $site;
			$response = $site->mvc->getResponse();
			$response->setStatus(404);
			$response->write('<h1>Oops, that\'s an error</h1>');
		}

		function redirAction() {
			global $site;
			$response = $site->mvc->getResponse();
			$response->redirect( $site->urlTo('/home') );
		}
	}

?>