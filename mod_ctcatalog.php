<?php
/**
 * CustomTables Joomla! 3.x Native Component
 * @package mod_ctcatalog.php
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link http://www.joomlaboat.com
 * @copyright (C) 2018-2024 Ivan Komlev
 * @license GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 **/

 // no direct access
defined('_JEXEC') or die('Restricted access');

use CustomTables\CT;
use CustomTables\Catalog;
use CustomTables\Params;

$path = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_customtables' 
	. DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR;

require_once($path.'loader.php');
CustomTablesLoader();

$params_array = Params::menuParamsRegistry2Array($params);

$ct = new CT($params_array, true, $module->id);

$ctCatalog = new Catalog($ct); //$params is the parameter passed by joomla to the module, it contains module settings

echo $ctCatalog->render();
