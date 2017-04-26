<?php
// namespace components\com_jmap\controllers;
/**
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage controllers
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
jimport ( 'joomla.filesystem.file' );
jimport ( 'joomla.filesystem.archive' );
jimport ( 'joomla.filesystem.stream' );

/**
 * Main controller class
 *
 * @package JMAP::SITEMAP::components::com_jmap
 * @subpackage controllers
 * @since 1.0
 */
class JMapControllerSitemap extends JMapController {
	/**
	 * Display the Sitemap
	 *
	 * @access public
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false) {
		// Get REQUEST vars all used to makeId for cache handler
		$option = $this->option;
		$format = JRequest::getVar ( 'format', 'html' );
		$language = JRequest::getvar ( 'lang', null );

		$itemid = JRequest::getInt ( 'Itemid', null );
		$xslt = JRequest::getInt ( 'xslt', null );
		$dataset = JRequest::getInt ( 'dataset', null );
		
		// Get sitemap model and view core
		$document = JFactory::getDocument ();
		$viewType = $document->getType ();
		$coreName = $this->getNames ();
		$viewLayout = JRequest::getCmd ( 'layout', 'default' );
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath 
		) );
		if($view === false) {
			$this->setRedirect ( "index.php?option=$option&task=sitemap.display" );
			return false;
		}
		
		// Get/Create the model
		if ($model = $this->getModel ( $coreName, 'JMapModel', array (
				'document_format' => $format,
				'jmap_module' => JRequest::getInt ( 'jmap_module', null )
		) )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}
		
		// Set model state
		$model->setState ( 'format', $format );
		$model->setState ( 'xslt', $xslt );
		
		// Set the layout
		$view->setLayout ( $viewLayout );
		
		// Display the view checking for cache feature
		$componentConfig = $model->getComponentParams ();
		$cachable = $componentConfig->get ( 'enable_view_cache', false );
		$preCaching = $componentConfig->get ( 'enable_precaching', false );

		/**
		 * Order of priorities:
		 * precached sitemap if any -->
		 * Joomla cache if any -->
		 * standard realtime generation
		 */
		// First requirement: a file exists for this requested precached sitemap
		$langString = $language ? '_' . $language : null;
		$ItemidFilter = $itemid ? '_menuid' . $itemid : null;
		$datasetFilter = $dataset ? '_dataset' . $dataset : null;
		$precachedSitemapFileName = JPATH_COMPONENT . '/precache/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
		if($preCaching && $format != 'html' && file_exists($precachedSitemapFileName)) {
			// Read the file and stream out directly
			$precachedBuffer = JFile::read($precachedSitemapFileName);
			// Is requested an xslt formatted view, so manage xsl adding
			if($xslt) {
				$xslFormat = $format == 'xml' ? null : $format . '-';
				$xslFile = "<?xml version='1.0' encoding='UTF-8'?>" . PHP_EOL . "<?xml-stylesheet type='text/xsl' href='" . JURI::root() . "components/com_jmap/xslt/xml-" . $xslFormat . "sitemap.xsl'?>";
				// Now add replace xsl to sitemap file
				$precachedBuffer = preg_replace(
									"/<\?xml version='1.0' encoding='UTF-8'\?>/i",
									$xslFile,
									$precachedBuffer);
			}
			// Finally stream out sitemap data
			$document->setMimeEncoding('application/xml');
			echo $precachedBuffer;
		} elseif ($cachable) {
			$registeredurlparams = new stdClass ();
			$registeredurlparams->lang = 'CMD';
			$registeredurlparams->dataset = 'INT';
			$registeredurlparams->Itemid = 'INT';
			$registeredurlparams->xslt = 'INT';
			$this->app->registeredurlparams = $registeredurlparams;
			if ($format != 'html') {
				$document->setMimeEncoding ( 'application/xml' );
			}
			
			$cache = $this->getExtensionCache ();
			$cache->get ( $view, 'display' );
		} else {
			$view->display ( $format );
		}
	}
	
	/**
	 * Export XML sitemap file
	 *
	 * @access public
	 * @return void
	 */
	public function exportXML() {
		// Get REQUEST vars
		$option = $this->option;
		$format = JRequest::getVar ( 'format', 'xml' );
		$jsClient = JRequest::getVar ( 'jsclient', false );
		$metainfoJsClient = JRequest::getVar ( 'metainfojsclient', false );
		$seospiderJsClient = JRequest::getVar ( 'seospiderjsclient', false );
		$cronjobClient = JRequest::getVar ( 'cronjobclient', false );
		
		// Manage language file string naming
		$lang = JRequest::getVar ( 'lang', null );
		$langString = $lang ? '_' . $lang : null;
		
		$Itemid = JRequest::getVar ( 'Itemid', null, 'default', 'INT' );
		$ItemidFilter = $Itemid ? '_menuid' . $Itemid : null;
		$dataset = JRequest::getVar  ( 'dataset', null, 'default', 'INT' );
		$datasetFilter = $dataset ? '_dataset' . $dataset : null;
		
		// Get sitemap model and view core
		$document = JFactory::getDocument ();
		$viewType = $document->getType ();
		$coreName = $this->getNames ();
		$viewLayout = JRequest::getCmd ( 'layout', 'default' );
		
		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath 
		) );
		if($view === false) {
			$this->setRedirect ( "index.php?option=$option&task=sitemap.display" );
			return false;
		}
		
		// Get/Create the model
		if ($model = $this->getModel ( $coreName, 'JMapModel', array (
				'document_format' => $format 
		) )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}

		// Set model state
		$model->setState ( 'format', $format );
		
		// Set the layout
		$view->setLayout ( $viewLayout );
		
		$cParams = JComponentHelper::getParams ( 'com_jmap' );
		// Display the view checking for cache feature
		$componentConfig = $model->getComponentParams ();
		$cachable = $cParams->get ( 'enable_view_cache', false );
		$preCaching = $cParams->get ( 'enable_precaching', false );
		
		// Start XML buffer
		ob_start ();
			/**
			 * Order of priorities:
			 * precached sitemap if any -->
			 * Joomla cache if any -->
			 * standard realtime generation
			 */
			// First requirement: a file exists for this requested precached sitemap
			$precachedSitemapFileName = JPATH_COMPONENT . '/precache/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
			$precachedSitemapDirectFile = false;
			if($preCaching && file_exists($precachedSitemapFileName)) {
				$precachedSitemapDirectFile = $precachedSitemapFileName;
				// Read the file and stream out directly
				$precachedBuffer = JFile::read($precachedSitemapFileName);
				echo $precachedBuffer;
			} elseif ($cachable) {
				$registeredurlparams = new stdClass ();
				$registeredurlparams->lang = 'CMD';
				$registeredurlparams->dataset = 'INT';
				$registeredurlparams->Itemid = 'INT';
				$registeredurlparams->xslt = 'INT';
				$this->app->registeredurlparams = $registeredurlparams;
				$cache = $this->getExtensionCache ();
				$cache->get ( $view, 'display' );
			} else {
				$view->display ( $format, true );
			}
			$xmlSitemap = ob_get_contents ();
		ob_end_clean ();
		
		// Choose if split sitemap, exclude rss and videos (including CDATA parsed from XML) and every kind of js client used for analyzer and metainfo
		if ($cParams->get ( 'split_sitemap', false ) && !$jsClient && !$metainfoJsClient && !$seospiderJsClient && !$cronjobClient && $format != 'rss' && $format != 'videos') {
			// Split the sitemap
			$splitter = new JMapXmlSplitter ( $format, $langString, $datasetFilter, $ItemidFilter );
			$splitter->chunkXMLString ( $xmlSitemap, 'url', $cParams->get ( 'split_chunks', 5 ), $precachedSitemapDirectFile );
			// Check if chunks was generated
			if ($xmlChunkFiles = $splitter->getChunks ()) {
				// Create ZIP archive and pass contents of written file to export
				$archiver = JArchive::getAdapter ( 'zip' );
				$pathForArchive = JPATH_COMPONENT_ADMINISTRATOR . '/cache/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.zip';
				$archiver->create ( $pathForArchive, $xmlChunkFiles );
				
				// Export download as attachment
				if (! $model->exportXMLSitemap ( $pathForArchive, $format, 'zip', $langString, $datasetFilter, $ItemidFilter, 'application/zip', true )) {
					$msg = 'COM_JMAP_ERROR_EXPORTING_SITEMAP';
					$this->setRedirect ( "index.php?option=$option&task=sitemap.display", JText::_ ( $msg ) );
				}
			}
		} elseif ($jsClient) {
			$pathForFile = JPATH_COMPONENT_ADMINISTRATOR . '/cache/analyzer/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
			// Write the current demanded sitemap in the cache analyzer folder
			$fileWritten = JFile::write($pathForFile, $xmlSitemap);
			
			// Response JSON object
			$jsAppResponse = new stdClass ();
			$jsAppResponse->result = $fileWritten;
			$document->setMimeEncoding('application/json');
			echo json_encode($jsAppResponse);
		} elseif ($metainfoJsClient) {
			$pathForFile = JPATH_COMPONENT_ADMINISTRATOR . '/cache/metainfo/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
			// Write the current demanded sitemap in the cache analyzer folder
			$fileWritten = JFile::write($pathForFile, $xmlSitemap);
			
			// Response JSON object
			$jsAppResponse = new stdClass ();
			$jsAppResponse->result = $fileWritten;
			$document->setMimeEncoding('application/json');
			echo json_encode($jsAppResponse);
		} elseif ($seospiderJsClient) {
			$pathForFile = JPATH_COMPONENT_ADMINISTRATOR . '/cache/seospider/sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
			// Write the current demanded sitemap in the cache analyzer folder
			$fileWritten = JFile::write($pathForFile, $xmlSitemap);
			
			// Response JSON object
			$jsAppResponse = new stdClass ();
			$jsAppResponse->result = $fileWritten;
			$document->setMimeEncoding('application/json');
			echo json_encode($jsAppResponse);
		} elseif ($cronjobClient) {
			$fileName = 'sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml';
			$pathForFile = JPATH_ROOT . '/' . $fileName;
			
			if ($cParams->get ( 'split_sitemap', false ) && $format != 'rss' && $format != 'videos') {
				// Split the sitemap
				$splitter = new JMapXmlSplitter ( $format, $langString, $datasetFilter, $ItemidFilter );
				$splitter->chunkXMLString ( $xmlSitemap, 'url', $cParams->get ( 'split_chunks', 5 ), $precachedSitemapDirectFile );
				// Check if chunks was generated
				if ($xmlChunkFiles = $splitter->getChunks ()) {
					if(!empty($xmlChunkFiles)) {
						foreach ($xmlChunkFiles as $sitemapFile) {
							$chunkFilePath = JPATH_ROOT . '/' . $sitemapFile['name'];
							$chunkFileData = $sitemapFile['data'];
							$fileName = $sitemapFile['name'];
							$pathForFile = $chunkFilePath;
							$fileWritten = JFile::write($chunkFilePath, $chunkFileData);
						}
					}
				}
			} else {
				// Write the current demanded sitemap to the website root
				$fileWritten = JFile::write($pathForFile, $xmlSitemap);
			}
			
			// XML Response to the client
			$document->setMimeEncoding('application/xml');
			if($fileWritten) {
				echo JText::sprintf('COM_JMAP_CRONJOB_FILEWRITTEN_SUCCESS', $fileName, 
									JPATH_ROOT,
									JUri::root(false) . $fileName,
									JUri::root(false) . $fileName,
									$pathForFile);
			} else {
				echo JText::sprintf('COM_JMAP_CRONJOB_FILEWRITTEN_ERROR', $fileName, 
									JPATH_ROOT);
			}
		} else {
			if (! $model->exportXMLSitemap ( $xmlSitemap, $format, 'xml', $langString, $datasetFilter, $ItemidFilter, 'application/xml' )) {
				$msg = 'COM_JMAP_ERROR_EXPORTING_SITEMAP';
				$this->setRedirect ( "index.php?option=$option&task=sitemap.display", JText::_ ( $msg ) );
			}
		}
	}
	
	/**
	 * Start a single iteration of a whole precaching process
	 * This controller.task is execute by a JS App Client and the number of
	 * iterations are managed by JS App
	 *
	 * @access public
	 * @return void
	 */
	public function doPreCaching() {
		// Get REQUEST vars
		$option = $this->option;
		$format = JRequest::getVar ( 'format', 'xml' );
		// Manage language file string naming
		$lang = JRequest::getVar ( 'lang', null );
		$langString = $lang ? '_' . $lang : null;

		$Itemid = JRequest::getVar ( 'Itemid', null, 'default', 'INT' );
		$ItemidFilter = $Itemid ? '_menuid' . $Itemid : null;
		$dataset = JRequest::getVar ( 'dataset', null, 'default', 'INT'  );
		$datasetFilter = $dataset ? '_dataset' . $dataset : null;
	
		// Params from javascript application async
		$dataSourceID = JRequest::getInt('datasource_id');
		$iterationCounter = JRequest::getInt('iteration_counter');

		// Get sitemap model and view core
		$document = JFactory::getDocument ();
		$viewType = $document->getType ();
		$coreName = $this->getNames ();
		$viewLayout = JRequest::getCmd ( 'layout', 'default' );

		$view = $this->getView ( $coreName, $viewType, '', array (
				'base_path' => $this->basePath
		) );
		if($view === false) {
			$this->setRedirect ( "index.php?option=$option&task=sitemap.display" );
			return false;
		}

		// Get/Create the model
		if ($model = $this->getModel ( $coreName, 'JMapModel', array (
				'document_format' => $format,
				'iteration_counter' => $iterationCounter
		) )) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}

		// Set model state
		$model->setState ( 'format', $format );
		$model->setState ( 'datasourceid', $dataSourceID );
			
		// Set the layout
		$view->setLayout ( $viewLayout );

		$cParams = JComponentHelper::getParams ( 'com_jmap' );
		// Start XML buffer
		$xmlSitemap = null;
		if($this->app->input->get ( 'process_status' ) != 'end') {
			ob_start ();
				$view->display ( $format, true );
				$xmlSitemap = ob_get_contents ();
			ob_end_clean ();
		}

		// Instance and <<use>> Precacher object
		$fileStreamWriter = new JMapFileStream();
		$preCacher = new JMapXmlPrecacher('sitemap_' . $format . $langString . $datasetFilter . $ItemidFilter . '.xml', $fileStreamWriter);
		$jsAppResponse = $preCacher->mergeSitemap($xmlSitemap);

		// Format json object for response, get exceptions from model and Precacher class
		$jsAppResponse->affected_rows = $model->getState( 'affected_rows' );

		$document->setMimeEncoding('application/json');
		echo json_encode($jsAppResponse);
	}
	
	/**
	 * Class Constructor
	 * 
	 * @access public
	 * @return Object&
	 */
	public function __construct($config = array()) {
		parent::__construct ( $config );
		$this->registerTask ( 'view', 'display' );
	}
}