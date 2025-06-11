<?php
/**
 * CustomTables Joomla! 3.x/4.x/5.x Component
 * @package mod_ctcatalog.php
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link https://www.joomlaboat.com
 * @copyright (C) 2018-2025 Ivan Komlev
 * @license GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 **/

// no direct access
defined('_JEXEC') or die('Restricted access');

use CustomTables\CT;
use CustomTables\Catalog;
use CustomTables\Params;
use CustomTables\common;
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

$path = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_customtables'
	. DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR;

require_once($path . 'loader.php');
CustomTablesLoader();

$menu_params = new Registry;//Joomla Specific
$menu_params->loadString($module->params);

$applyURLQueryParameters = (int)$menu_params->get('applyurlqueryparameters');

$ct = new CT([], !(bool)$applyURLQueryParameters);

$menu_paramsArray = Params::menuParamsRegistry2Array($menu_params);
$ct->Params->setParams($menu_paramsArray);
$ct->Params->ModuleId = $module->id;

$ctCatalog = new Catalog($ct); //$params is the parameter passed by joomla to the module, it contains module settings
$result = $ctCatalog->render();

if ($ct->Table === null) {
	Factory::getApplication()->enqueueMessage('Custom Tables - Catalog Module: Table not found');
} else {
	common::loadJSAndCSS($ct->Params, $ct->Env, $ct->Table->fieldInputPrefix);

	$app = Factory::getApplication();

	if (!empty($ctCatalog->layoutCodeCSS))
		$app->getDocument()->addCustomTag('<style>' . $ctCatalog->layoutCodeCSS . '</style>');

	if (!empty($ctCatalog->layoutCodeJS))
		$app->getDocument()->addCustomTag('<script>' . $ctCatalog->layoutCodeJS . '</script>');

	echo $result;
}