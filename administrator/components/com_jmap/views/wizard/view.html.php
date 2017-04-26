<?php
// namespace administrator\components\com_jmap\views\wizard;
/**
 * @package JMAP::WIZARD::administrator::components::com_jmap
 * @subpackage views
 * @subpackage wizard
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
 
/**
 * Wizard view class. Only use is to render wizard control panel for supported extensions data source creation 
 * 
 * @package JMAP::WIZARD::administrator::components::com_jmap
 * @subpackage views
 * @subpackage wizard
 * @since 2.0
 */
class JMapViewWizard extends JMapView {
	/**
	 * Render default user defined icon for cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return string
	 */
	private function renderCustomDatasourceIcon() {
		$lang =  JFactory::getLanguage ();
		$langDirection = $lang->isRTL() ? 'right' : 'left';
		$dataContent = JText::_('COM_JMAP_CREATE_CUSTOM_DATASOURCE_DESC');
		$text = JText::_('COM_JMAP_CREATE_CUSTOM_DATASOURCE');
		$link = JFilterOutput::ampReplace('index.php?option=com_jmap&task=sources.editentity');
		$imageIcon = JHTML::_('image.site',  'custom.png', '/components/com_jmap/images/wizard/', NULL, NULL, JText::_('COM_JMAP_CREATE_CUSTOM_DATASOURCE'));
		$iconSnippet = <<<HTML
			<div style="float: $langDirection;">
				<div class="icon hasPopover" data-content="$dataContent">
					<a href="$link">
						$imageIcon
						<span>$text</span>
					</a>
				</div>
			</div>
HTML;
		return $iconSnippet;
	}

	/**
	 * Render plugin data source type icon for cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return string
	 */
	private function renderPluginDatasourceIcon() {
		$lang =  JFactory::getLanguage ();
		$langDirection = $lang->isRTL() ? 'right' : 'left';
		$dataContent = JText::_('COM_JMAP_CREATE_PLUGIN_DATASOURCE_DESC');
		$text = JText::_('COM_JMAP_CREATE_PLUGIN_DATASOURCE');
		$link = JFilterOutput::ampReplace('index.php?option=com_jmap&task=sources.editentity&type=plugin');
		$imageIcon = JHTML::_('image.site',  'plugin.png', '/components/com_jmap/images/wizard/', NULL, NULL, JText::_('COM_JMAP_CREATE_PLUGIN_DATASOURCE'));
		$iconSnippet = <<<HTML
			<div style="float: $langDirection;">
				<div class="icon hasPopover" data-content="$dataContent">
					<a href="$link">
						$imageIcon
						<span>$text</span>
					</a>
				</div>
			</div>
HTML;
		return $iconSnippet;
	}

	/**
	 * Render links data source type icon for cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return string
	 */
	private function renderLinksDatasourceIcon() {
		$lang =  JFactory::getLanguage ();
		$langDirection = $lang->isRTL() ? 'right' : 'left';
		$dataContent = JText::_('COM_JMAP_CREATE_LINKS_DATASOURCE_DESC');
		$text = JText::_('COM_JMAP_CREATE_LINKS_DATASOURCE');
		$link = JFilterOutput::ampReplace('index.php?option=com_jmap&task=sources.editentity&type=links');
		$imageIcon = JHTML::_('image.site',  'links.png', '/components/com_jmap/images/wizard/', NULL, NULL, JText::_('COM_JMAP_CREATE_LINKS_DATASOURCE'));
		$iconSnippet = <<<HTML
			<div style="float: $langDirection;">
				<div class="icon hasPopover" data-content="$dataContent">
					<a href="$link">
						$imageIcon
						<span>$text</span>
					</a>
				</div>
			</div>
HTML;
		return $iconSnippet;
	}

	/**
	 * Render iconset for cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return void
	 */
	private function renderIcon($link, $dataSourceName, $dataSourceUserViewName, $extensionName) {
		$lang =  JFactory::getLanguage ();
		?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon hasPopover" data-content="<?php echo sprintf(JText::_('COM_JMAP_CREATE_DATASOURCE'), $dataSourceUserViewName);?>">
				<a data-role="start_create_process" data-extension="<?php echo $extensionName; ?>" href="<?php echo JFilterOutput::ampReplace($link); ?>">
					<?php echo JHTML::_('image.site', 'icon.png?nocache='.$dataSourceName, '/components/com_jmap/images/wizard/'.$dataSourceName.'/', NULL, NULL, $dataSourceUserViewName ); ?>
					<span><?php echo $dataSourceUserViewName; ?></span>
				</a>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		$doc = JFactory::getDocument();
		$doc->addStyleDeclaration('.icon-48-jmap{background-image:url("components/com_jmap/images/icon-48-wizard.png")}');
		JToolBarHelper::title( JText::_('COM_JMAP_JMAPWIZARD' ), 'jmap' );
		JToolBarHelper::custom('sources.display', 'data', 'data', 'COM_JMAP_DATA_SOURCES', false);
		JToolBarHelper::custom('cpanel.display', 'config', 'config', 'COM_JMAP_CPANEL', false);
	}
	
	/**
	 * Default display that renders wizard icons control panel and inject JS APP
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		$doc = $this->document;
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$base = JURI::base();
		$doc->addStylesheet ( JURI::root ( true ) . '/administrator/components/com_jmap/css/wizard.css' );
		$doc->addScript ( JURI::root ( true ) . '/administrator/components/com_jmap/js/wizard.js' );
		$doc->addScriptDeclaration("var jmap_baseURI='$base';");
		
		// Inject js translations
		$translations = array(	'COM_JMAP_PROGRESSINFOTITLE1', 
								'COM_JMAP_PROGRESSINFOSUBTITLE1',
								'COM_JMAP_PROGRESSINFOSUBTITLE1_2',
								'COM_JMAP_PROGRESSINFOSUBTITLE1_2ERROR',
								'COM_JMAP_PROGRESSINFOTITLE2',
								'COM_JMAP_PROGRESSINFOSUBTITLE2',
								'COM_JMAP_PROGRESSINFOSUBTITLE2_2');
		$this->injectJsTranslations($translations, $doc);
		
		// get Filter Input to mor security safe
		$filterInput = JFilterInput::getInstance();
		$discoveredExtensions = $this->getModel()->getData(JPATH_COMPONENT . '/images/wizard');
		
		// Buffer delle icons
		ob_start ();
		if(!empty($discoveredExtensions)) {
			foreach ($discoveredExtensions as $discoveredExtension) {
				$dataSourceName = $discoveredExtension['dataSourceName'];
				$extensionName = $discoveredExtension['extensionName']; 
				$dataSourceUserViewName = ucfirst(str_replace('_', ' ', $dataSourceName));
				$this->renderIcon ( 'index.php?option=com_jmap&task=wizard.createEntity&extension=' . $filterInput->clean($dataSourceName, 'CMD'), $dataSourceName, $dataSourceUserViewName, $extensionName);
			}
		}
		$contents = ob_get_clean ();
		 
		// Assign reference variables
		$this->icons = $contents;
		$this->customIcon = $this->renderCustomDatasourceIcon();
		$this->pluginIcon = $this->renderPluginDatasourceIcon();
		$this->linksIcon = $this->renderLinksDatasourceIcon();
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		// Output del template
		parent::display ();
	}
}