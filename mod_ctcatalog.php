<?php

/**
 * CustomTables Joomla! 3.x Native Component
 * @version 1.0.0
 * @author Ivan komlev <support@joomlaboat.com>
 * @link http://www.joomlaboat.com
 * @license GNU/GPL
 **/

 // no direct access
defined('_JEXEC') or die('Restricted access');
    
$site_path=JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customtables'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR;
$admin_path=JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customtables'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR;
	
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customtables'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'catalog.php');
require_once($site_path.'layout.php');
require_once($admin_path.'layouts.php');
require_once($admin_path.'misc.php');

require_once($site_path.'tagprocessor'.DIRECTORY_SEPARATOR.'catalogtag.php');
require_once($site_path.'tagprocessor'.DIRECTORY_SEPARATOR.'catalogtableviewtag.php');

require_once($site_path.'tagprocessor'.DIRECTORY_SEPARATOR.'generaltags.php');
require_once($site_path.'tagprocessor'.DIRECTORY_SEPARATOR.'pagetags.php');
require_once($site_path.'tagprocessor'.DIRECTORY_SEPARATOR.'itemtags.php');
require_once($site_path.'tagprocessor'.DIRECTORY_SEPARATOR.'fieldtags.php');


		$jinput=JFactory::getApplication()->input;

		$result='';

		$_params= $params;//new JRegistry;
		JoomlaBasicMisc::prepareSearchFilter($_params);
		
		$config=array();
		$model = JModelLegacy::getInstance('Catalog', 'CustomTablesModel', $config);
		$model->load($_params, true);
		$model->showpagination=0;
		
		$type=0;
		$pagelayout=ESLayouts::getLayout($params->get( 'ct_pagelayout' ),$type);
		if($pagelayout=='')
			$pagelayout='{catalog:,notable}';
		
		$itemlayout=ESLayouts::getLayout($params->get( 'ct_itemlayout' ),$type);
		
		
		$SearchResult=$model->getSearchResult();
		$catalogtablecode=JoomlaBasicMisc::generateRandomString();//this is temporary replace place holder. to not parse content result again

		$catalogtablecontent=tagProcessor_CatalogTableView::process($model,$pagelayout,$SearchResult,$catalogtablecode);
		if($catalogtablecontent=='')
		{
		    $model->LayoutProc->layout=$itemlayout;
			$catalogtablecontent=tagProcessor_Catalog::process($model,$pagelayout,$SearchResult,$catalogtablecode);
		}

		$model->LayoutProc->layout=$pagelayout;
		$pagelayout=$model->LayoutProc->fillLayout(array(), null, '');
		$pagelayout=str_replace('&&&&quote&&&&','"',$pagelayout); // search boxes may return HTMl elemnts that contain placeholders with quotes like this: &&&&quote&&&&
		$pagelayout=str_replace($catalogtablecode,$catalogtablecontent,$pagelayout);

		if($params->get( 'allowcontentplugins' )==1)
			LayoutProcessor::applyContentPlugins($pagelayout);
		
		echo $pagelayout;

