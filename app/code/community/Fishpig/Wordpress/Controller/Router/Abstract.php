<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

abstract class Fishpig_Wordpress_Controller_Router_Abstract extends Mage_Core_Controller_Varien_Router_Standard
{
	/**
	 * Stores the static routes used by WordPress
	 *
	 * @var array
	 */
	protected $_staticRoutes = array();
	
	/**
	 * Used to provide classwide access to the request object
	 *
	 * @var null|Zend_Controller_Request_Http
	 */
	protected $_requestObject = null;
	
	/**
	 * The name of the router for the extension
	 * This is used to easily set routes and to define the router
	 *
	 * @var string
	 */
	protected $_frontendRouterName = 'wordpress';
	
	/**
	 * Used to load controllers
	 * We could look this up in the config cache, but hardcoding is quicker
	 *
	 * @var string
	 */
	protected $_controllerClassPrefix = 'Fishpig_Wordpress';

	/**
	 * Performs the logic for self::match
	 * This is where the route is calculated
	 *
	 * @param string $uri
	 * @return bool
	 */
	abstract protected function _match($uri);

	/**
	 * Create an instance of the router and add it to the queue
	 *
	 * @param Varien_Event_Observer $observer
	 * @return bool
	 */	
	public function initControllerObserver(Varien_Event_Observer $observer)
	{
		$routerClass = get_class($this);

   	    $observer->getEvent()->getFront()
   	    	->addRouter($this->_frontendRouterName, new $routerClass);
   	    
   	    return true;
	}
		
	/**
	 * Determine whether it is okay to try and match the route
	 *
	 * @return bool
	 */
	protected function _canMatchRoute()
	{
		return Mage::helper('wordpress')->isFullyIntegrated() 
			&& Mage::app()->getStore()->getCode() !== 'admin';
	}
	
	/**
	 * Attempt to match the current URI to this module
	 * If match found, set module, controller, action and dispatch
	 *
	 * @param Zend_Controller_Request_Http $request
	 * @return bool
	 */
	public function match(Zend_Controller_Request_Http $request)
	{
		try {
			if ($this->_canMatchRoute()) {
				$this->_requestObject = $request;

				if (($uri = Mage::helper('wordpress/router')->getBlogUri()) !== null) {
					$this->_initStaticRoutes();

					if (($staticRoute = $this->_matchStaticRoute($uri)) !== false) {
						$this->setRoutePath($staticRoute);
					}
					else {
						$this->_match($uri);
					}
					
					if ($this->_hasValidRouteDetails()) {
						return $this->_dispatch();
					}
				}
			}
		}
		catch (Exception $e) { 
			Mage::helper('wordpress')->log($e->getMessage());
		}

		return false;
	}
	
	/**
	 * Quickly set the module, controller and action
	 *
	 * @param string $path
	 * @param array $params = array
	 * @return bool
	 */
	public function setRoutePath($path, array $params = array())
	{
		Mage::dispatchEvent('wordpress_route_path_set', array('router' => $this, 'path' => $path, 'params' => $params));
		
		$parts = explode('/', $path);
		
		if (count($parts) === 3) {
			list($module, $controller, $action) = $parts;
			
			if ($module === '*') {
				$module = $this->_frontendRouterName;
			}

			$this->getRequest()->setModuleName($module)
				->setRouteName($module)
				->setControllerName($controller)
				->setActionName($action);
			
			foreach($params as $key => $value) {
				$this->getRequest()->setParam($key, $value);
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Determine whether valid route details have been set
	 *
	 * @return bool
	 */
	protected function _hasValidRouteDetails()
	{
		$request = $this->getRequest();
		
		return $request->getModuleName() 
			&& $request->getControllerName() 
			&& $request->getActionName();
	}

	/**
	 * Dispatch the controller request
	 *
	 * @return bool
	 */
	protected function _dispatch()
	{
		$request = $this->getRequest();
		
		if ($controllerClassName = $this->_validateControllerClassName($this->_controllerClassPrefix, $request->getControllerName())) {
			$controllerInstance = new $controllerClassName($request, $this->getFront()->getResponse());

			if ($controllerInstance->hasAction($request->getActionName())) {
				$request->setDispatched(true);
				$controllerInstance->dispatch($request->getActionName());
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Retrieve the controller class filename
	 * This method allows Addon WP modules to use their own controller
	 *
	 * @param string $realModule
	 * @param string $controller
	 * @return string
	 */
	public function getControllerFileName($realModule, $controller)
	{
		if (substr_count($realModule, '_') > 1) {
			return Mage::getModuleDir('controllers', $realModule)
				 . DS . uc_words($controller, DS) . 'Controller.php';
		}

		return parent::getControllerFileName($realModule, $controller);
	}
	
	/**
	 * Initliase the static routes used by WordPress
	 *
	 * @return $this
	 */
	protected function _initStaticRoutes()
	{
		Mage::dispatchEvent($this->_frontendRouterName . '_init_static_routes_after', array('router' => $this));
		
		return $this;
	}
	
	/**
	 * Register a static route
	 *
	 * @param string $route - internal token used by the controller
	 * @param string $alias - route used in the URL
	 */
	public function addStaticRoute($pattern, $action = 'index', $controller = 'index', $module = '*')
	{
		$this->_staticRoutes[$pattern] = array(
			'module' => $module,
			'controller' => $controller,
			'action' => $action,
		);
		
		return $this;
	}
	
	/**
	 * Checks to see whether the URI matches any of the static routes
	 *
	 * @param string $uri
	 * @return string
	 */
	protected function _matchStaticRoute($uri)
	{	
		foreach($this->_staticRoutes as $pattern => $route) {
			if (preg_match($pattern, $uri, $matches)) {
				return implode('/', $route);
			}
		}
		
		return false;
	}
	
	/**
	 * Returns the current request object
	 *
	 * @return Zend_Controller_Request_Http
	 */
	public function getRequest()
	{
		return $this->_requestObject;
	}
	
	/**
	 * Set the controller clas prefix
	 * This is used when loading a controller class
	 *
	 * @param string $prefix
	 * @return $this
	 */
	public function setControllerClassPrefix($prefix)
	{
		$this->_controllerClassPrefix = $prefix;
		
		return $this;
	}
}
