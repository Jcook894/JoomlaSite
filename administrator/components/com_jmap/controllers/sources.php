<?php
// namespace administrator\components\com_jmap\controllers;
/**
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

/**
 * Main sitemap sources controller manager
 * @package JMAP::SOURCES::administrator::components::com_jmap
 * @subpackage controllers
 * @since 1.0
 */
class JMapControllerSources extends JMapController {
	/**
	 * Set model state from session userstate
	 * @access protected
	 * @param string $scope
	 * @return void
	 */
	protected function setModelState($scope = 'default', $ordering = true) {
		$option = $this->option;
	
		// Get default model
		$defaultModel = $this->getModel();
	
		$filter_state = $this->getUserStateFromRequest ( "$option.sources.filterstate", 'filter_state', '*' );
		$filter_type = $this->getUserStateFromRequest ( "$option.sources.filtertype", 'filter_type', '' );
		parent::setModelState($scope, true);
	
		// Set model state
		$defaultModel->setState('state', $filter_state); 
		$defaultModel->setState('type', $filter_type);
	
		return $defaultModel;
	}
	
	/**
	 * Default listEntities
	 * 
	 * @access public
	 * @param $cachable string
	 *       	 the view output will be cached
	 * @return void
	 */
	function display($cachable = false, $urlparams = false) {
		// Set model state
		$defaultModel = $this->setModelState('sources');
		 
		// Parent construction and view display
		parent::display($cachable);
	}
	  
	
	/**
	 * Edit entity
	 *
	 * @access public
	 * @return void
	 */
	public function editEntity() { 
		JRequest::setVar('hidemainmenu', 1);  
		$option = $this->option;
		$cid = JRequest::getVar('cid', array(0), 'method', 'array');
		$idEntity = (int) $cid[0];
		$user = $this->user;
		
		$model = $this->getModel();
		$model->setState('option', $option);
		
		// Try to load record from model 
		if(!$record = $model->loadEntity($idEntity)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelExceptions = $model->getErrors();
			foreach ($modelExceptions as $exception) {
				$this->app->enqueueMessage($exception->getMessage(), $exception->getErrorLevel());
			}
			$this->setRedirect ( 'index.php?option=com_jmap&task=sources.display');
			return false;
		}
		
		// Check if model record ORM table needs a type swap based on plugin type data source
		if(JRequest::getVar('type', null) === 'plugin') {
			$record->type = 'plugin';
		}
		
		// Check if model record ORM table needs a type swap based on plugin type data source
		if ($this->app->input->get ( 'type', null ) === 'links') {
			$record->type = 'links';
		}
		
		// Check out control on record
		if ($record->checked_out && $record->checked_out != $user->id) {
			$this->setRedirect ( 'index.php?option=' . $option . '&task=sources.display', JText::_('COM_JMAP_CHECKEDOUT_RECORD'), 'notice');
			return false;
		}
		
		// Access check
		if($record->id && !$this->allowEdit($model->getState('option'))) {
			$this->setRedirect('index.php?option=com_jmap&task=sources.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
		
		if(!$record->id && !$this->allowAdd($model->getState('option'))) {
			$this->setRedirect('index.php?option=com_jmap&task=sources.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
		
		// Check out del record
		if ($record->id) {
			$record->checkout ( $user->id );
		}
		
		// Get view and pushing model
		$view = $this->getView();
		$view->setModel ( $model, true );
		
		// Call edit view
		$view->editEntity($record); 
	}
  
	/**
	 * Manage entity apply/save after edit entity
	 *
	 * @access public
	 * @return void
	 */
	public function saveEntity() {
		// Ensure magic quotes is not active to preserve SQL Compiler work
		@ini_set('magic_quotes_runtime', 0);
		
		$task = JRequest::getCmd('task', 'saveEntity');
		$option = $this->option;
		$context = implode('.', array($option, strtolower($this->getNames()), 'errordataload')); 
		$regenerateQuery = JRequest::getVar('regenerate_query', false);
		$sqlQuerymanagedChunks = JRequest::getVar('params', null);
		$targetExtension = $sqlQuerymanagedChunks['datasource_extension'];
		
		// Security layer for tags html outputted fields
		$sanitizedFields = array('name', 'description');
		foreach ($sanitizedFields as $field) {
			$_POST[$field] = strip_tags($_POST[$field]);
		}
		$_POST['params']['title'] = strip_tags($_POST['params']['title']);
		
		// Get sources model and make dependency injection
		$wizardModel = $this->getModel('Wizard', 'JMapModel', array('extension' => $targetExtension, 'sourcesModel' => null));
		
		//Load della  model e bind store
		$model = $this->getModel ();
		
		if(!$result = $model->storeEntity($regenerateQuery, false, $wizardModel)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			
			// Store data for session recover
			$this->app->setUserState($context, $_POST);
			$this->setRedirect ( 'index.php?option=com_jmap&task=sources.editEntity&cid[]='.JRequest::getVar('id'), JText::_('COM_JMAP_ERROR_SAVING'));
			return false;
		}

		// Security safe if not model record id detected
		if(!$id = $result->id) {
			$id = JRequest::getVar('id');
		}
		
		$redirects = $task == 'sources.saveEntity' ? array('task'=>'display', 'msgsufix'=>'_SAVING') : array('task'=>'editEntity&cid[]=' . $id, 'msgsufix'=>'_APPLY');
		$msg = 'COM_JMAP_SUCCESS' . $redirects['msgsufix'];
		$controllerTask = $redirects['task'];
	
		$this->setRedirect ( "index.php?option=$option&task=sources.$controllerTask", JText::_($msg));
	}
	
	/**
	 * Manage cancel edit for entity and unlock record checked out
	 *
	 * @access public
	 * @return void
	 */
	public function cancelEntity() { 
		$id = JRequest::getVar('id');
		$option = $this->option;
		//Load della  model e checkin before exit
		$model = $this->getModel ( );
		
		if(!$model->cancelEntity($id)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
		}
		 
		$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_CANCELED_OPERATION') );
	}
	
	/**
	 * Copies one or more items
	 * 
	 * @access public
	 * @return void
	 */
	public function copyEntity() { 
		$cids = JRequest::getVar ( 'cid', null, 'post', 'array' ); 
		$option = $this->option;
		//Load della  model e checkin before exit
		$model = $this->getModel ( );
		
		if(!$model->copyEntity($cids)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_ERROR_DUPLICATING'));
			return false;
		}
		
		$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_SUCCESS_DUPLICATING'));
	}
	
	/**
	 * Delete a db table entity
	 *
	 * @access public
	 * @return void
	 */
	public function deleteEntity() {
		$cids = JRequest::getVar('cid', array(0), 'method', 'array');
		$option = $this->option;
		$additionalNotice = null;
		// Access check
		if(!$this->allowDelete($option)) {
			$this->setRedirect('index.php?option=com_jmap&task=sources.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
		//Load della  model e checkin before exit
		$model = $this->getModel ( );
		
		// Ensure not deleting Content source if multiple mode is enabled
		$cParams = $model->getComponentParams();
		if($cParams->get('multiple_content_sources', 0)) {
			$foundKey = array_search(1, $cids);
			if ($foundKey !== false) {
				array_splice($cids, $foundKey, 1, null);
				$additionalNotice = JText::_('COM_JMAP_NOTALLOWED_CONTENT_DELETE');
			}
		}
		
		if(!$model->deleteEntity($cids)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_ERROR_DELETE'));
			return false;
		}
	
		$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_SUCCESS_DELETE') . $additionalNotice);
	}
	
	/**
	 * Moves the order of a record
	 * 
	 * @access public
	 * @param integer The increment to reorder by
	 * @return void
	 */
	public function moveOrder() {
		// Set model state
		$this->setModelState('sources');
		$option = $this->option;
		// ID Entity
		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		$idEntity = $cid[0];
		// Task direction
		$model = $this->getModel();
		$orderDir = $model->getState('order_dir');
		
		switch ($orderDir) {
			case 'desc':
				$orderUp = 1;
				$orderDown = -1;
				break;
				
			case 'asc':
			default:
				$orderUp = -1;
				$orderDown = 1;
				break;
		}
		
		$direction = $this->task == 'moveorder_up' ? $orderUp : $orderDown;
		
		if(!$model->changeOrder($idEntity, $direction)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_ERROR_REORDER'));
			return false;
		}
		
		$this->setRedirect("index.php?option=$option&task=sources.display", JText::_('COM_JMAP_SUCCESS_REORDER'));
	}
	
	/**
	 * Save ordering
	 *
	 * @access public
	 * @return void
	 */
	public function saveOrder() { 
		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		$option = $this->option;
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);
	
		$model = $this->getModel();
		
		if(! $model->saveOrder($cid, $order)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_ERROR_REORDER'));
			return false;
		}
		
		$this->setRedirect("index.php?option=$option&task=sources.display",  JText::_('COM_JMAP_SUCCESS_REORDER'));
	}
	
	/**
	 * Publishing entities
	 * 
	 * @access public
	 * @return void
	 */
	public function publishEntities() {
		// Access check
		if(!$this->allowEditState(JRequest::getVar('option'))) {
			$this->setRedirect('index.php?option=com_jmap&task=sources.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
		
		$cid = JRequest::getVar('cid', array(0), 'method', 'array');
		$idEntity = (int) $cid[0];
		$option = $this->option;
		
		$model = $this->getModel();
		
		if(! $model->publishEntities($idEntity, $this->task)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$modelException = $model->getError(null, false);
			$this->app->enqueueMessage($modelException->getMessage(), $modelException->getErrorLevel());
			$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_ERROR_STATE_CHANGE'));
			return false;
		}
		
		$this->setRedirect( "index.php?option=$option&task=sources.display",  JText::_('COM_JMAP_SUCCESS_STATE_CHANGE'));
	}
	
	/**
	 * Export sources as db table entities
	 *
	 * @access public
	 * @return void
	 */
	public function exportEntities() {
		$cids = JRequest::getVar('cid', array(0), 'method', 'array');
		$option = $this->option;
		// Access check
		if(!$this->allowEdit($option)) {
			$this->setRedirect('index.php?option=com_jmap&task=sources.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}

		// Get the file manager instance with db connector dependency injection
		$filesManager = new JMapFileSources(JFactory::getDbo(), $this->app);

		if(!$filesManager->export($cids)) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$filesManagerException = $filesManager->getError(null, false);
			$this->app->enqueueMessage($filesManagerException->getMessage(), $filesManagerException->getErrorLevel());
			$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_ERROR_EXPORT'));
			return false;
		}
	
		$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_SUCCESS_EXPORT'));
	}
	
	/**
	 * Export sources as db table entities
	 *
	 * @access public
	 * @return void
	 */
	public function importEntities() {
		$option = $this->option;
		// Access check
		if(!$this->allowEdit($option)) {
			$this->setRedirect('index.php?option=com_jmap&task=sources.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}
	
		// Get the file manager instance with db connector dependency injection
		$filesManager = new JMapFileSources(JFactory::getDbo(), $this->app);
	
		if(!$filesManager->import()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$filesManagerException = $filesManager->getError(null, false);
			$this->app->enqueueMessage($filesManagerException->getMessage(), $filesManagerException->getErrorLevel());
			$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_ERROR_IMPORT'));
			return false;
		}
	
		$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_SUCCESS_IMPORT'));
	}
	
	/**
	 * Export sources as db table entities
	 *
	 * @access public
	 * @return void
	 */
	public function importPlugins() {
		$option = $this->option;
		// Access check
		if(!$this->allowAdd($option)) {
			$this->setRedirect('index.php?option=com_jmap&task=sources.display', JText::_('COM_JMAP_ERROR_ALERT_NOACCESS'), 'notice');
			return false;
		}

		// Get the file manager instance with db connector dependency injection
		$filesManager = new JMapFileSources(JFactory::getDbo(), $this->app);

		if(!$filesManager->install()) {
			// Model set exceptions for something gone wrong, so enqueue exceptions and levels on application object then set redirect and exit
			$filesManagerException = $filesManager->getError(null, false);
			$this->app->enqueueMessage($filesManagerException->getMessage(), $filesManagerException->getErrorLevel());
			$this->setRedirect ( "index.php?option=$option&task=sources.display", JText::_('COM_JMAP_ERROR_INSTALLING_PLUGIN'));
			return false;
		}

		// After importing files forward request to save data source
		$this->app->enqueueMessage(JText::_('COM_JMAP_PLUGIN_INSTALL_SUCCESSFUL'));

		$this->task = 'applyEntity';
		$this->saveEntity();
	}
	
	/**
	 * Class Constructor
	 * 
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );
		// Register Extra tasks
		$this->registerTask ( 'moveorder_up', 'moveOrder' );
		$this->registerTask ( 'moveorder_down', 'moveOrder' );
		$this->registerTask ( 'applyEntity', 'saveEntity' );
		$this->registerTask ( 'unpublish', 'publishEntities' );
		$this->registerTask ( 'publish', 'publishEntities' );
	}
}