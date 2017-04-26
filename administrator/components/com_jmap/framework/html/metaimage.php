<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;
jimport('joomla.form.field');
jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla CMS.
 * Provides a modal media selector including upload mechanism
 *
 * @since  1.6
 */
class JMapHtmlMetaimage extends JFormField {
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = 'Metaimage';

	/**
	 * The initialised state of the document object.
	 *
	 * @var    boolean
	 */
	protected static $initialised = false;

	/**
	 * The authorField.
	 *
	 * @var    string
	 */
	protected $preview;

	/**
	 * The preview.
	 *
	 * @var    string
	 */
	protected $directory;
	
	/**
	 * The data ajax identifier.
	 *
	 * @var    int
	 */
	protected $dataIdentifier;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'preview':
			case 'directory':
			case 'dataIdentifier':
				return $this->$name;
				
			case 'input':
				$this->input = $this->getInput();
				return $this->input;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value) {
		switch ($name) {
			case 'class' :
				$value = preg_replace ( '/\s+/', ' ', trim ( ( string ) $value ) );
			
			case 'preview' :
			case 'directory' :
			case 'description' :
			case 'hint' :
			case 'value' :
			case 'labelclass' :
			case 'onchange' :
			case 'onclick' :
			case 'validate' :
			case 'pattern' :
			case 'group' :
			case 'class' :
			case 'default' :
				$this->$name = ( string ) $value;
				break;
			
			case 'id' :
				$this->id = $this->getId ( ( string ) $value, $this->fieldname );
				break;
			
			case 'fieldname' :
				$this->fieldname = $this->getFieldName ( ( string ) $value );
				break;
			
			case 'name' :
				$this->fieldname = $this->getFieldName ( ( string ) $value );
				$this->name = $this->getName ( $this->fieldname );
				break;
			
			case 'multiple' :
				$value = ( string ) $value;
				$value = $value === '' && isset ( $this->forceMultiple ) ? ( string ) $this->forceMultiple : $value;
			
			case 'required' :
			case 'disabled' :
			case 'readonly' :
			case 'autofocus' :
			case 'hidden' :
				$value = ( string ) $value;
				$this->$name = ($value === 'true' || $value === $name || $value === '1');
				break;
			
			case 'autocomplete' :
				$value = ( string ) $value;
				$value = ($value == 'on' || $value == '') ? 'on' : $value;
				$this->$name = ($value === 'false' || $value === 'off' || $value === '0') ? false : $value;
				break;
			
			case 'spellcheck' :
			case 'translateLabel' :
			case 'translateDescription' :
			case 'translateHint' :
				$value = ( string ) $value;
				$this->$name = ! ($value === 'false' || $value === 'off' || $value === '0');
				break;
			
			case 'translate_label' :
				$value = ( string ) $value;
				$this->translateLabel = $this->translateLabel && ! ($value === 'false' || $value === 'off' || $value === '0');
				break;
			
			case 'translate_description' :
				$value = ( string ) $value;
				$this->translateDescription = $this->translateDescription && ! ($value === 'false' || $value === 'off' || $value === '0');
				break;
			
			case 'dataIdentifier' :
			case 'size' :
				$this->$name = ( int ) $value;
				break;
			
			default :
				if (property_exists ( __CLASS__, $name )) { } else {
					$this->$name = $value;
				}
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see 	JFormField::setup()
	 * @since   3.2
	 */
	public function setup(&$element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		return $result;
	}

	/**
	 * Method to get the field input markup for a media selector.
	 * Use attributes to identify specific created_by and asset_id fields
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		if (!self::$initialised)
		{
			// Load the modal behavior script.
			JHtml::_('behavior.modal');

			// Build the script.
			$script = array();
			$script[] = '	function jInsertFieldValue(value, id) {';
			$script[] = '		var $ = jQuery.noConflict();';
			$script[] = '		var old_value = $("#" + id).val();';
			$script[] = '		if (old_value != value) {';
			$script[] = '			var $elem = $("#" + id);';
			$script[] = '			$elem.val(value);';
			$script[] = '			$elem.trigger("change");';
			$script[] = '			if (typeof($elem.get(0).onchange) === "function") {';
			$script[] = '				$elem.get(0).onchange();';
			$script[] = '			}';
			$script[] = '			jMediaRefreshPreview(id);';
			$script[] = '			var parentRow = $("#" + id).parents("tr");';
			$script[] = '			JMapMetainfo.refreshRowStatus(parentRow, $elem.data("mediaidentifier"));';
			$script[] = '		}';
			$script[] = '	}';

			$script[] = '	function jMediaRefreshPreview(id) {';
			$script[] = '		var $ = jQuery.noConflict();';
			$script[] = '		var value = $("#" + id).val();';
			$script[] = '		if(!value)return;';
			$script[] = '		value = value.replace(new RegExp("^[/]+"), "")';
			$script[] = '		value = value.match(/http/g) ? value : "'. JUri::root() . '" + value';
			$script[] = '		var $img = $("#" + id + "_preview");';
			$script[] = '		if ($img.length) {';
			$script[] = '			if (value) {';
			$script[] = '				$img.attr("src", value);';
			$script[] = '				$("#" + id + "_preview_empty").hide();';
			$script[] = '				$("#" + id + "_preview_img").show()';
			$script[] = '			} else { ';
			$script[] = '				$img.attr("src", "");';
			$script[] = '				$("#" + id + "_preview_empty").show();';
			$script[] = '				$("#" + id + "_preview_img").hide();';
			$script[] = '			} ';
			$script[] = '		} ';
			$script[] = '	}';

			$script[] = '	function jMediaRefreshPreviewTip(tip)';
			$script[] = '	{';
			$script[] = '		var $ = jQuery.noConflict();';
			$script[] = '		var $tip = $(tip);';
			$script[] = '		var $img = $tip.find("img.media-preview");';
			$script[] = '		$tip.find("div.tip").css("max-width", "none");';
			$script[] = '		var id = $img.attr("id");';
			$script[] = '		id = id.substring(0, id.length - "_preview".length);';
			$script[] = '		jMediaRefreshPreview(id);';
			$script[] = '		$tip.show();';
			$script[] = '	}';

			// Add the script to the document head.
			JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));

			self::$initialised = true;
		}

		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : 'class="mediaimagefield"';
		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// The text field.
		$html[] = '<div class="fltlft">';
		$html[] = '	<input type="text" data-mediaidentifier="' . $this->dataIdentifier . '" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $attr . ' />';
		$html[] = '</div>';

		$directory = (string) $this->element['directory'];
		if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value))
		{
			$folder = explode('/', ltrim($this->value, '/'));
			array_shift($folder);
			array_pop($folder);
			$folder = implode('/', $folder);
		}
		elseif (file_exists(JPATH_ROOT . '/' . JComponentHelper::getParams('com_media')->get('image_path', 'images') . '/' . $directory))
		{
			$folder = $directory;
		}
		else
		{
			$folder = '';
		}
		// The button.
		$html[] = '<div class="button2-left">';
		$html[] = '	<div class="blank">';
		$html[] = '		<a class="modal" title="' . JText::_('COM_JMAP_METAINFO_FORM_BUTTON_SELECT') . '"' . ' href="'
			. ($this->element['readonly'] ? ''
			: ('index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;author='
				. 'jmap') . '&amp;fieldid=' . $this->id . '&amp;folder=' . $folder) . '"'
			. ' rel="{handler: \'iframe\', size: {x: 1024, y: 640}}">';
		$html[] = JText::_('COM_JMAP_METAINFO_FORM_BUTTON_SELECT') . '</a>';
		$html[] = '	</div>';
		$html[] = '</div>';

		$html[] = '<div class="button2-left">';
		$html[] = '	<div class="blank">';
		$html[] = '		<a title="' . JText::_('COM_JMAP_METAINFO_FORM_BUTTON_CLEAR') . '"' . ' href="#" onclick="';
		$html[] = 'jInsertFieldValue(\'\', \'' . $this->id . '\');';
		$html[] = 'return false;';
		$html[] = '">';
		$html[] = JText::_('COM_JMAP_METAINFO_FORM_BUTTON_CLEAR') . '</a>';
		$html[] = '	</div>';
		$html[] = '</div>';

		// The Preview.
		$preview = (string) $this->element['preview'];
		$showPreview = true;
		$showAsTooltip = false;
		switch ($preview)
		{
			case 'false':
			case 'none':
				$showPreview = false;
				break;
			case 'true':
			case 'show':
				break;
			case 'tooltip':
			default:
				$showAsTooltip = true;
				$options = array(
					'onShow' => 'jMediaRefreshPreviewTip',
				);
				JHtml::_('behavior.tooltip', '.hasTipPreview', $options);
				break;
		}

		if ($showPreview)
		{
			if ($this->value && file_exists(JPATH_ROOT . '/' . $this->value))
			{
				$src = JURI::root() . $this->value;
			}
			else
			{
				$src = '';
			}

			$attr = array(
				'id' => $this->id . '_preview',
				'class' => 'media-preview',
				'style' => 'max-width:160px; max-height:100px;'
			);
			$img = JHtml::image($src, JText::_('COM_JMAP_METAINFO_MEDIA_PREVIEW_ALT'), $attr);
			$previewImg = '<div id="' . $this->id . '_preview_img"' . ($src ? '' : ' style="display:none"') . '>' . $img . '</div>';
			$previewImgEmpty = '<div id="' . $this->id . '_preview_empty"' . ($src ? ' style="display:none"' : '') . '>'
				. JText::_('COM_JMAP_METAINFO_MEDIA_PREVIEW_EMPTY') . '</div>';

			$html[] = '<div class="media-preview fltlft">';
			if ($showAsTooltip)
			{
				$tooltip = $previewImgEmpty . $previewImg;
				$options = array(
					'title' => JText::_('COM_JMAP_METAINFO_MEDIA_PREVIEW_SELECTED_IMAGE'),
					'text' => JText::_('COM_JMAP_METAINFO_MEDIA_PREVIEW_TIP_TITLE'),
					'class' => 'hasTipPreview glyphicon glyphicon-eye-open label label-default'
				);
				$html[] = JHtml::tooltip($tooltip, $options);
			}
			else
			{
				$html[] = ' ' . $previewImgEmpty;
				$html[] = ' ' . $previewImg;
			}
			$html[] = '</div>';
		}


		return implode("\n", $html);
	}
}
