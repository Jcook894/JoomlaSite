<html>
  <head>
    <meta name="google-site-verification" content="FSrcrTTfBQatR_ytzgq5j-r8lv5i4tYgBdOEguByosY" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

  </head>
</html>
<?php
/*
 * ------------------------------------------------------------------------
 * JA Purity II template for Joomla 2.5
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - GNU/GPL, http://www.gnu.org/licenses/gpl.html
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites: http://www.joomlart.com - http://www.joomlancers.com
 * ------------------------------------------------------------------------
*/
// no direct access

defined ( '_JEXEC' ) or die ( 'Restricted access' ); 

if (class_exists('T3Template')) {
  $tmpl = T3Template::getInstance();
  $tmpl->setTemplate($this);
  $tmpl->render();
  return;
} else {
  //Need to install or enable JAT3 Plugin
  echo JText::_('Missing jat3 framework plugin');
}