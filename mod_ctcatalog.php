<?php
/**
 * CustomTables Joomla! 3.x Native Component
 * @author Ivan komlev <support@joomlaboat.com>
 * @link http://www.joomlaboat.com
 * @license GNU/GPL
 **/

 // no direct access
defined('_JEXEC') or die('Restricted access');

use CustomTables\CT;
use CustomTables\Layouts;

$path = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_customtables' 
	. DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR;

require_once($path.'loader.php');
CTLoader();
    
$site_path=JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customtables'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR;

$path=JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customtables'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'customtables'.DIRECTORY_SEPARATOR;
require_once($path.'loader.php');
CTLoader();

require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customtables'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'catalog.php');
require_once($site_path.'layout.php');

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
		
		$pagelayout='{catalog:,notable}';
		
		if($params->get( 'ct_pagelayout' )!=null)
		{
			$ct = new CT;
			$Layouts = new Layouts($ct);
			$pagelayout=$Layouts->getLayout($params->get( 'ct_pagelayout' ));
			if($pagelayout=='')
				$pagelayout='{catalog:,notable}';
		}
		
		$itemlayout=$Layouts->getLayout($params->get( 'ct_itemlayout' ));
		
		
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

