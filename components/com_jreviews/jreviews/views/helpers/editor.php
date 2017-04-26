<?php
/**
 * JReviews - Reviews Extension
 * Copyright (C) 2010-2011 ClickFWD LLC
 * This is not free software, do not distribute it.
 * For licencing information visit http://www.reviewsforjoomla.com
 * or contact sales@reviewsforjoomla.com
 * 
 * This is the default display for custom fields
 **/
defined( 'MVC_FRAMEWORK') or die( 'Direct Access to this location is not allowed.' );

class EditorHelper extends MyHelper
{				
	var $helpers = array('html');
	var $editor = 'tinyMCE';	
	
	function load($inline = false) 
    {
		$this->Html->app = $this->app;
		$directionality = cmsFramework::isRTL() ? 'rtl' : 'ltr';
        
		switch($this->editor) {
			
			case 'tinyMCE':
				$this->Html->js(array('tiny_mce/tiny_mce'),$inline);								

				# Initialize editor
				$editorInit = '<script type="text/javascript">
					tinyMCE.init({						
						theme : "advanced",
						language : "en",
						mode : "none",
						gecko_spellcheck : true,
						document_base_url : "'.WWW_ROOT.'",
//						entities : "60,lt,62,gt",
                        entity_encoding : "raw",
						relative_urls : true,
						remove_script_host : false,
		//				save_callback : "TinyMCE_Save",
						invalid_elements : "script,applet,iframe",
        //              extended_valid_elements : "a[class|name|href|target|title|onclick|rel],img[float|class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[id|title|alt|class|width|size|noshade]",
						theme_advanced_toolbar_location : "top",
						theme_advanced_statusbar_location : "bottom",
						theme_advanced_resizing : true,
						theme_advanced_resize_horizontal : true,
						directionality: "'.$directionality.'",
						force_br_newlines : false,
						force_p_newlines : true,
                        forced_root_block : "p",
                        content_css: "'.WWW_ROOT.'templates/'.cmsFramework::getTemplate().'/css/template.css",
						debug : false,
						cleanup : true,
						cleanup_on_startup : false,
						safari_warning : false,
						//plugins : "advlink, advimage, searchreplace,insertdatetime,media,advhr,table,fullscreen,directionality,layer,style",
						plugin_insertdate_dateFormat : "%Y-%m-%d",
						plugin_insertdate_timeFormat : "%H:%M:%S",						
						fullscreen_settings : {
							theme_advanced_path_location : "top"
						}
					});
					</script>';
					cmsFramework::addScript($editorInit,$inline);			
				break;
			
			
                case 'JCE':
					
					cmsFramework::addScript('<link rel="stylesheet" href="'.WWW_ROOT.'components/com_jce/editor/libraries/css/editor.css'.'" type="text/css" />',$inline);

                    cmsFramework::addScript('<script type="text/javascript" src="'.WWW_ROOT.'components/com_jce/editor/tiny_mce/tiny_mce.js"></script>',$inline);
					
                    cmsFramework::addScript('<script type="text/javascript" src="'.WWW_ROOT.'components/com_jce/editor/libraries/js/editor.js"></script>',$inline);
                    
                    $editorInit = '<script type="text/javascript">   
try{
		WFEditor.init({
			token: "'.EditorHelper::getToken().'",
			base_url: "'.WWW_ROOT.'",
			language: "en",
			directionality: "ltr",
			theme: "advanced",
			plugins: "advlist,autolink,cleanup,code,format,lists,tabfocus,wordcount,imgmanager,paste,textcase,directionality,fullscreen,preview,source,print,searchreplace,table,visualchars,nonbreaking,style,xhtmlxtras,link,spellchecker,article,browser,contextmenu,inlinepopups,media",
			component_id: 22,
			theme_advanced_buttons1: "help,newdocument,undo,redo,|,bold,italic,underline,strikethrough,justifyfull,justifycenter,justifyleft,justifyright,|,blockquote,formatselect,styleselect,removeformat,cleanup",
			theme_advanced_buttons2: "fontselect,fontsizeselect,forecolor,backcolor,|,imgmanager,indent,cut,copy,paste,outdent,numlist,bullist,sub,sup,textcase,charmap,hr",
			theme_advanced_buttons3: "ltr,rtl,fullscreen,preview,source,wrap,highlight,numbers,print,search,replace,|,table_insert,delete_table,|,row_props,cell_props,|,row_before,row_after,delete_row,|,col_before,col_after,delete_col,|,split_cells,merge_cells",
			theme_advanced_buttons4: "visualaid,visualchars,nonbreaking,style,cite,abbr,acronym,del,ins,attribs,anchor,unlink,link,spellchecker,readmore,pagebreak",
			theme_advanced_toolbar_location: "top",
			theme_advanced_toolbar_align: "left",
			theme_advanced_statusbar_location: "bottom",
			theme_advanced_resizing: true,
			jquery_ui: "/jreviewsdev25/components/com_jce/editor/libraries/css/jquery/jce/jquery-ui.custom.css",
			content_css: "/jreviewsdev25/templates/ireview/css/template.css",
			toggle_label: "[Toggle Editor]",
			verify_html: false,
			table_inline_editing: true,
			fix_list_elements: true,
			invalid_elements: "iframe,script,style,applet,body,bgsound,base,basefont,frame,frameset,head,html,id,ilayer,layer,link,meta,name,title,xml",
			entity_encoding: "raw",
			removeformat_selector: "p,h1,h2,h3,h4,h5,h6,address,code,pre,samp,span,b,strong,em,i,font,u,strike",
			theme_advanced_blockformats: {"advanced.paragraph":"p","advanced.div":"div","advanced.h1":"h1","advanced.h2":"h2","advanced.h3":"h3","advanced.h4":"h4","advanced.h5":"h5","advanced.h6":"h6","advanced.address":"address","advanced.code":"code","advanced.pre":"pre","advanced.samp":"samp","advanced.span":"span"},
			formats: {span : {inline : \'span\'}},
			remove_script_host: false,
			theme_advanced_fonts: "Andale Mono=andale mono,times;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats",
			theme_advanced_font_sizes: "8pt,10pt,12pt,14pt,18pt,24pt,36pt",
			theme_advanced_default_foreground_color: "#000000",
			theme_advanced_default_background_color: "#FFFF00",
			spellchecker_languages: "+English=en",
			spellchecker_rpc_url: "/jreviewsdev25/index.php?option=com_jce&view=editor&layout=plugin&plugin=spellchecker&component_id=22",
			file_browser_callback: function(name, url, type, win){tinyMCE.activeEditor.plugins.browser.browse(name, url, type, win);},
			compress: {"javascript":0,"css":0}
        });
	
	} catch(e){alert(e);}

                    </script>';  
					
                    cmsFramework::addScript($editorInit,$inline);            
					
			
                    
                break;
		}
	}
	
	function transform($return = false) 
    {
        switch($this->editor) 
        {
			case 'tinyMCE':				

				if($return == true) {
					return "jQuery('.wysiwyg_editor').tinyMCE();";
				} else {
					cmsFramework::addScript("<script type='text/javascript'>jQuery(document).ready(function() {jQuery('.wysiwyg_editor').tinyMCE();});</script>");
				}				

			break;
			
			case 'JCE':                

				if($return == true) {
					return "setTimeout(function(){jQuery('.wysiwyg_editor').tinyMCE();},1000);";
				} else {
					cmsFramework::addScript("<script type='text/javascript'>jQuery(document).ready(function() {setTimeout(function(){jQuery('.wysiwyg_editor').tinyMCE();},1000);});</script>");
				}				
				
				
			break;
		}
	}
	
	function remove() {
		
		switch($this->editor) {			
			case 'tinyMCE':
					return "jQuery('.wysiwyg_editor').RemoveTinyMCE();";				
				break;
            case 'JCE':                                        
                    return "jQuery('.wysiwyg_editor').RemoveTinyMCE();";                
                break;				
		}		
		
	}
	
	private function _createToken( $length = 32 )
	{
		static $chars = '0123456789abcdef';
		$max      = strlen( $chars ) - 1;
		$token      = '';
		$name       =  session_name();
		for( $i = 0; $i < $length; ++$i ) {
			$token .= $chars[ (rand( 0, $max )) ];
		}

		return md5($token.$name);
	}

	public static function getToken()
	{
		$session  =JFactory::getSession();
		$user     =JFactory::getUser();
		$token    = $session->get('session.token', null, 'wf');

		//create a token
		if ( $token === null) {
			$token = self::_createToken(12);
			$session->set('session.token', $token, 'wf');
		}

		$hash = 'wf' . JUtility::getHash($user->get( 'id', 0 ) . $token);

		return $hash;
	}	
	
}