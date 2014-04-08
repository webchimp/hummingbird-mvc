<?php

	/**
	 * Hummingbird MVC (Codename Cinnamon)
	 *
	 * A simple, yet powerful MVC layer for hummingbird, it has controllers, models, views and that
	 * kind of stuff that makes developers go bonkers and code awesome apps.
	 *
	 * @version 1.4
	 * @author  biohzrdmx <github.com/biohzrdmx>
	 * @license MIT
	 */

	// ============================================================================================
	//   Model Class
	// ============================================================================================

	/**
	 * Model class - The 'M' on MVC
	 *
	 * A simple wrapper for data models, it's mainly empty for now but will contain some awesome
	 * functionality on future versions. You must override the init() method.
	 */
	abstract class Model {

		/**
		 * Constructor
		 */
		function __construct() {
			$this->init();
		}

		/**
		 * Initialization callback, must be overriden in your extended classes
		 */
		abstract function init();
	}

	// ============================================================================================
	//   View Class
	// ============================================================================================

	/**
	 * View class - The 'V' on MVC
	 *
	 * The View class has all the required functionality to render your templates (and to know where
	 * it would find those templates). Just make sure to override the init() method.
	 */
	abstract class View {

		/**
		 * Holds the (absolute) path to the templates folder for this instance, you may override this in the init() method of your extended classes
		 * @var string
		 */
		protected $pages_dir;

		/**
		 * Holds the (absolute) path to the parts folder for this instance, you may override this in the init() method of your extended classes
		 * @var string
		 */
		protected $parts_dir;

		/**
		 * Constructor
		 */
		function __construct() {
			global $site;
			$this->pages_dir = $site->baseDir('/pages');
			$this->parts_dir = $site->baseDir('/parts');
			$this->init();
		}

		/**
		 * Render a template
		 * @param string $template Template name, may contain relative path information ('index', 'stuff/index')
		 * @param array  $data     Array of data to be passed to the template, keys will become variable names when imported into scope
		 * @param string $dir      Path to override templates path
		 */
		function render($template, $data = array(), $dir = null) {
			global $site;
			$request = $site->mvc->getRequest();
			$dir = $dir ? $dir : $this->pages_dir;
			$include = sprintf('%s/%s.php', $dir, $template);
			# Check whether the template exists or not
			if ( file_exists($include) ) {
				# Expand data
				extract($data, EXTR_SKIP);
				# Set body slug
				$site->addBodyClass($request->controller);
				$site->addBodyClass($request->controller . '-' . $request->action);
				$site->addBodyClass( trim( str_replace('/', '-', $template), '-' ) );
				# Import globals
				extract($GLOBALS, EXTR_REFS | EXTR_SKIP);
				# Hide function parameters
				unset($data);
				unset($template);
				# Include file
				include $include;
			} else {
				$site->errorMessage("View error: template '{$template}' does not exist.");
				exit;
			}
		}

		/**
		 * Render a partial
		 * @param string $partial  Partial name, may contain relative path information ('item', 'stuff/item')
		 * @param array  $data     Array of data to be passed to the template, keys will become variable names when imported into scope
		 * @param string $dir      Path to override partials path
		 */
		function partial($partial, $data = array(), $dir = null) {
			global $site;
			$request = $site->mvc->getRequest();
			$dir = $dir ? $dir : $this->parts_dir;
			$div = strrpos($partial, '/');
			$path = substr($partial, 0, $div);
			$file = substr($partial, ++$div);
			$partial = $path ? "{$path}/_{$file}" : "_{$file}";
			$include = sprintf('%s/%s.php', $dir, $partial);
			# Check whether the template exists or not
			if ( file_exists($include) ) {
				# Expand data
				extract($data, EXTR_SKIP);
				# Import globals
				extract($GLOBALS, EXTR_REFS | EXTR_SKIP);
				# Hide function parameters
				unset($data);
				unset($partial);
				# Include file
				include $include;
			} else {
				echo "<div>View error: partial '{$partial}' does not exist.</div>";
			}
		}

		/**
		 * Set the value of a data item
		 * @param string $name  Name of the data item
		 * @param mixed  $value Value of the data item
		 */
		function setData($name, $value) {
			$this->data[$name] = $value;
		}

		/**
		 * Get the value of a data item, if set
		 * @param  string $name    Name of the data item
		 * @param  string $default Default value to return if the item isn't set
		 * @return mixed           Item value or $default
		 */
		function getData($name, $default = '') {
			return isset( $this->data[$name] ) ? $this->data[$name] : $default;
		}

		/**
		 * Initialization callback, must be overriden in your extended classes
		 */
		abstract function init();
	}

	// ============================================================================================
	//   Controller Class
	// ============================================================================================

	/**
	 * Controller class - The 'C' in MVC
	 *
	 * As the Controller, this will take the requests and perform appropiate actions (call a model's
	 * method, render a view, send a file to the user, etc.). Extend and add your own actions, and
	 * don't forget to override init().
	 */
	abstract class Controller {

		/**
		 * Holds the list of aliases for controller actions
		 * @var array
		 */
		protected $aliases;

		/**
		 * Holds the list of chained controllers for the current instance
		 * @var array
		 */
		protected $chains;

		/**
		 * Constructor
		 */
		function __construct() {
			$this->aliases = array();
			$this->init();
		}

		/**
		 * Add an alias for an action, the router will check for aliases before defaulting to 'show'
		 * @param string $action Action function name (e.g. 'showAction')
		 * @param string $alias  Alias for the action (e.g. 'view', so you go to '/controller/view' and 'showAction' gets called)
		 */
		function addActionAs($action, $alias) {
			$this->aliases[$alias] = $action;
		}

		/**
		 * Get the function name for the given alias (if any)
		 * @param  string $alias Action alias
		 * @return mixed         Function name or Null if the alias does not exist
		 */
		function getActionAs($alias) {
			if ( isset( $this->aliases[$alias] ) ) {
				return $this->aliases[$alias];
			} else {
				return null;
			}
		}

		/**
		 * Checks whether the specified controller (or alias) is chained or not
		 * @param  string  $controller Controller name or alias
		 * @return boolean             True if the controller is chained, False otherwise
		 */
		function isControllerChained($controller) {
			return isset( $this->chains[$controller] );
		}

		/**
		 * Get a chained controller (or alias) class name
		 * @param  string $controller Controller name or alias
		 * @return mixed              Controller class name or Null on error
		 */
		function getChainedController($controller) {
			return isset( $this->chains[$controller] ) ? $this->chains[$controller] : null;
		}

		/**
		 * Add a chained controller by name or alias
		 * @param string $controller Controller name
		 * @param string $alias      Controller alias, optional
		 */
		function addChainedController($controller, $alias = null) {
			$key = $alias ? $alias : strtolower($controller);
			$this->chains[$key] = $controller;
		}

		/**
		 * Initialization callback, must be overriden in your extended classes
		 */
		abstract function init();
	}

	// ============================================================================================
	//   Request Class
	// ============================================================================================

	/**
	 * Request class
	 *
	 * This class holds the Request in a convenient way and has also some helper methods to get
	 * variables and check HTTPS easily.
	 *
	 * Has no overrideable methods.
	 */
	class Request {

		/**
		 * The format specifier for the expected response (html, json, xml, yaml, etc.)
		 * @var string
		 */
		public $format;

		/**
		 * HTTP method used to make the current request (get, post, etc.)
		 * @var string
		 */
		public $type;

		/**
		 * Request controller
		 * @var string
		 */
		public $controller;

		/**
		 * Request action
		 * @var string
		 */
		public $action;

		/**
		 * Request id
		 * @var string
		 */
		public $id;

		/**
		 * Request parts (controller, action, id and extra fragments)
		 * @var string
		 */
		public $parts;

		/**
		 * Constructor
		 */
		function __construct() {
			$this->format = 'html';
			$this->type = 'get';
			$this->controller = 'index';
			$this->action = 'index';
			$this->id = '';
			$this->parts = array();
		}

		/**
		 * Check whether the current request is secure (HTTPS) or not
		 * @return boolean True if the request was made via HTTPS, False otherwise
		 */
		function secure() {
			return isset( $_SERVER['HTTPS'] );
		}

		/**
		 * Get a variable from the $_REQUEST superglobal
		 * @param  string $name    Variable name
		 * @param  string $default Default value to return if the variable is not set
		 * @return mixed           Variable value or $default
		 */
		function param($name, $default = '') {
			return isset( $_REQUEST[$name] ) ? $_REQUEST[$name] : $default;
		}

		/**
		 * Get a variable from the $_GET superglobal
		 * @param  string $name    Variable name
		 * @param  string $default Default value to return if the variable is not set
		 * @return mixed           Variable value or $default
		 */
		function get($name, $default = '') {
			return isset( $_GET[$name] ) ? $_GET[$name] : $default;
		}

		/**
		 * Get a variable from the $_POST superglobal
		 * @param  string $name    Variable name
		 * @param  string $default Default value to return if the variable is not set
		 * @return mixed           Variable value or $default
		 */
		function post($name, $default = '') {
			return isset( $_POST[$name] ) ? $_POST[$name] : $default;
		}

		/**
		 * Get a variable from the $_SESSION superglobal
		 * @param  string $name    Variable name
		 * @param  string $default Default value to return if the variable is not set
		 * @return mixed           Variable value or $default
		 */
		function session($name, $default = '') {
			return isset( $_SESSION[$name] ) ? $_SESSION[$name] : $default;
		}

		/**
		 * Get a file from the $_FILES superglobal
		 * @param  string $name File key
		 * @return mixed        Array with file properties or Null
		 */
		function files($name) {
			return isset( $_FILES[$name] ) ? $_FILES[$name] : null;
		}
	}

	// ============================================================================================
	//   Response Class
	// ============================================================================================

	/**
	 * Response class
	 *
	 * If there's a Request class then there must be a Response one (it's a matter or fact!), this
	 * object will hold the Response data (headers, code and body) and will spit it out automagically
	 * when the routing has ended. And it can do redirects too!
	 *
	 * Nothing to override there...
	 */
	class Response {

		/**
		 * Current response body
		 * @var string
		 */
		protected $body;

		/**
		 * Current response status code (HTTP status)
		 * @var integer
		 */
		protected $status;

		/**
		 * Current response headers
		 * @var array
		 */
		protected $headers;

		/**
		 * Constructor
		 */
		function __construct() {
			$this->body = '';
			$this->status = 200;
			$this->headers = array();
		}

		/**
		 * Write to the current response body, appends data
		 * @param  string $data Raw response data
		 */
		function write($data) {
			$this->body .= $data;
		}

		/**
		 * Set the body for the current response, replaces contents (if any)
		 * @param string $data Raw response body
		 */
		function setBody($data) {
			$this->body = $data;
		}

		/**
		 * Get the status code for the current response
		 * @return integer Current response status code
		 */
		function getStatus() {
			return $this->status;
		}

		/**
		 * Set the status code for the current response
		 * @param integer $code A valid HTTP response code (200, 404, 500, etc.)
		 */
		function setStatus($code) {
			$this->status = $code;
		}

		/**
		 * Get the current response body
		 * @return string The response body
		 */
		function getBody() {
			return $this->body;
		}

		/**
		 * Set the value of an specific header for the current response
		 * @param string $name  Header name
		 * @param string $value Header value
		 */
		function setHeader($name, $value) {
			$this->headers[$name] = $value;
		}

		/**
		 * Get the value of an specific header for the current response
		 * @param  string $name Header name
		 * @return mixed        Header value or Null if it's not set
		 */
		function getHeader($name) {
			return isset( $this->headers[$name] ) ? $this->headers[$name] : null;
		}

		/**
		 * Get the array of headers for the current response
		 */
		function getHeaders() {
			return $this->headers;
		}

		/**
		 * Do an HTTP redirection
		 * @param  string $url URL to redirect to
		 */
		function redirect($url) {
			header("Location: {$url}");
			exit;
		}

		/**
		 * Flush headers and response body
		 * @return boolean This will always return True
		 */
		function respond() {
			header('Status', true, $this->status);
			# Send headers
			foreach ($this->headers as $header => $value) {
				header("{$header}: {$value}");
			}
			# Send response
			echo $this->getBody();
			return true;
		}
	}

	// ============================================================================================
	//   MVC Class
	// ============================================================================================

	/**
	 * MVC class - The MVC in... wait, it has no sense...
	 *
	 * This does all the required routing and holds a list of controller aliases. Also hosts the
	 * Request and Response objects and handles controller chaining.
	 *
	 * Has no overrideable methods.
	 */
	class MVC {

		/**
		 * Current request object
		 * @var Request
		 */
		protected $request;

		/**
		 * Current response object
		 * @var Response
		 */
		protected $response;

		/**
		 * Holds the list of controller aliases for the current instance
		 * @var array
		 */
		protected $aliases;

		/**
		 * Holds the default controller class name (if any)
		 * @var string
		 */
		protected $default;

		/**
		 * Constructor
		 */
		function __construct() {
			global $site;
			# Register routes and disengage default site router
			$site->removeRoute('/:page');
			$site->addRoute('/:controller', 'MVC::router');
			$site->addRoute('/:controller/:action', 'MVC::router');
			$site->addRoute('/:controller/:action/*id', 'MVC::router');
			# Request & response variables
			$this->request = new Request();
			$this->response = new Response();
			# Route aliases
			$this->aliases = array();
			# Default controller
			$this->default = null;
		}

		/**
		 * Register an alias for a controller, useful for multilanguage sites or complex controller schemes
		 * @param string $controller Controller class name
		 * @param string $alias      Controller alias
		 */
		function addControllerAs($controller, $alias) {
			$this->aliases[$alias] = $controller;
		}

		/**
		 * Get the class name for the given alias (if any)
		 * @param  string $alias Controller alias
		 * @return mixed         Class name or Null if the alias does not exist
		 */
		function getControllerAs($alias) {
			if ( isset( $this->aliases[$alias] ) ) {
				return $this->aliases[$alias];
			} else {
				return null;
			}
		}

		/**
		 * Set the default controller
		 * @param string $controller Slug of the default controller (e.g. for 'SiteController' it would be 'site')
		 */
		function setDefaultController($controller) {
			$this->default = $controller;
		}

		/**
		 * Router helper function, does the actual routing
		 * @param  array   $params      Request parameters (controller, action, id, etc.)
		 * @param  object  $chain       Request object to be used for chaining
		 * @return boolean              True if a suitable route was found, False otherwise
		 */
		static function routeRequest($params, $chain = null) {
			global $site;
			$mvc = $site->mvc;
			# Extract parameters
			$controller = isset( $params[1] ) ? $params[1] : 'index';
			$action =     isset( $params[2] ) ? $params[2] : 'index';
			$id =         isset( $params[3] ) ? $params[3] : '';
			# Check whether the request may be handled by a controller or not
			$controllerClass = ucfirst("{$controller}Controller");
			$controllerClass = str_replace(' ', '', ucwords(str_replace('-', ' ', $controllerClass)));
			$instance = null;
			if ( class_exists( $controllerClass ) ) {
				$instance = new $controllerClass;
			} else if ( isset( $mvc->aliases[$controller] ) ) {
				$controllerClass = $mvc->aliases[$controller];
				$instance = new $controllerClass;
			}
			if ( $instance ) {
				# Relay to the controller
				$method = "{$action}Action";
				$method = str_replace(' ', '', ucwords(str_replace('-', ' ', $method)));
				$method[0] = strtolower( $method[0] );
				$alias = $instance->getActionAs($action);
				$method = method_exists($instance, $method) ? $method : ($alias ? $alias : 'showAction');  // check existing methods, then check aliases, then default to 'show'
				# Check controller chaining
				$sub = explode('/', $id);
				if ( count($sub) > 0 && $instance->isControllerChained( $sub[0] ) ) {
					# Get the name of the chained controller
					$chained = $instance->getChainedController( $sub[0] );
					# Prepare routing parameters
					$chain_params = array('');                              // add an empty item
					$chain_params[] = strtolower($chained);                 // set the new controller
					$chain_params[] = isset( $sub[1] ) ? $sub[1] : 'index'; // set the new action
					$chain_params[] = isset( $sub[2] ) ? $sub[2] : '';      // set the new id
					if ( empty( $chain_params[3] ) ) {
						unset( $chain_params[3] );                          // delete the id if it's empty
					}
					$chain_obj = new Request();                             // create a new request
					$chain_obj->controller = $controller;                   // use original controller name
					$chain_obj->action = 'show';                            // this will always be show
					$chain_obj->id = $action;                               // and this will be the index/key of an item
					$chain_obj->parts = array_slice($params, 1);            // add any extra fragment
					self::routeRequest($chain_params, $chain_obj);          // call this method again but now in chained mode (with the new request)
					exit;
				}
				# Check action
				if ($method == 'showAction' && $method != $action) {
					$id = $action;
					$action = 'show';
				}
				# Prepare request object
				$matches = null;
				if ( preg_match('/(\w+)\.(\w+)$/', $id, $matches) === 1 ) {
					$id = $matches[1];
					$mvc->request->format = $matches[2];
				}
				$mvc->request->type = strtolower( isset( $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ) ? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] : $_SERVER['REQUEST_METHOD'] );
				$mvc->request->controller = $controller;
				$mvc->request->action = $action;
				$mvc->request->id = $id;
				$mvc->request->parts = array_slice($params, 1);
				if ($chain) {
					$mvc->request->chain = $chain;
				}
				# Call action handler
				if ( method_exists($instance, $method) ) {
					ob_start();
					$instance->$method($id);
					$mvc->response->write( ob_get_clean() );
					return $mvc->response->respond();
				} else {
					$site->errorMessage("Router error: method '{$method}' from '{$controllerClass}' class does not exist.");
					return true;
				}
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Router
		 * @param  array  $params Array of router parameters
		 * @return boolen         True if the routing took place, False otherwise
		 */
		static function router($params) {
			global $site;
			$mvc = $site->mvc;
			if ( self::routeRequest($params) ) {
				return true;
			} else {
				# Check whether there are a default controller or not
				if ( $mvc->default ) {
					# Route the request through the default controller
					array_splice($params, 1, 0, $mvc->default);
					self::routeRequest($params);
					return true;
				} else {
					# Serve a static page
					return $site->getPage($params);
				}
			}
		}

		/**
		 * Get the Request object
		 * @return object Request object
		 */
		function getRequest() {
			return $this->request;
		}

		/**
		 * Get the Response object
		 * @return object Response object
		 */
		function getResponse() {
			return $this->response;
		}

	}

	# Initialize plugin
	$site->mvc = new MVC();

?>