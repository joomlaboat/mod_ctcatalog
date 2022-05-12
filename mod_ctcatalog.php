<?php
/**
 * CustomTables Joomla! 3.x Native Component
 * @package mod_ctcatalog.php
 * @author Ivan Komlev <support@joomlaboat.com>
 * @link http://www.joomlaboat.com
 * @copyright (C) 2018-2022 Ivan Komlev
 * @license GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
 **/

 // no direct access
defined('_JEXEC') or die('Restricted access');

use CustomTables\CT;
use CustomTables\Layouts;

$path = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_customtables' 
	. DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'customtables' . DIRECTORY_SEPARATOR;

require_once($path.'loader.php');
CTLoader();

$ct = new CT;
$ct->Env->menu_params = $params; //$params is the parameter passed by joomla to the module, it contains module settings

if ($ct->Env->legacysupport) {

    $site_path=JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customtables'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR;

    require_once($site_path . 'layout.php');
    require_once($site_path . 'tagprocessor' . DIRECTORY_SEPARATOR . 'catalogtag.php');
    require_once($site_path . 'tagprocessor' . DIRECTORY_SEPARATOR . 'catalogtableviewtag.php');
    require_once($site_path . 'tagprocessor' . DIRECTORY_SEPARATOR . 'generaltags.php');
    require_once($site_path . 'tagprocessor' . DIRECTORY_SEPARATOR . 'pagetags.php');
    require_once($site_path . 'tagprocessor' . DIRECTORY_SEPARATOR . 'itemtags.php');
    require_once($site_path . 'tagprocessor' . DIRECTORY_SEPARATOR . 'fieldtags.php');
}

// --------------------- ItemId
$forceItemId=$params->get('forceitemid');
if(isset($forceItemId) and $forceItemId!='')
{
    //Find Itemid by alias
    if(((int)$forceItemId)>0)
        $ct->Env->Itemid=$forceItemId;
    else
    {
        if($forceItemId!="0")
            $ct->Env->Itemid=(int)JoomlaBasicMisc::FindItemidbyAlias($forceItemId);//Accepts menu Itemid and alias
        else
            $ct->Env->Itemid=$ct->Env->jinput->get('Itemid',0,'INT');
    }
}
else
    $ct->Env->Itemid = $ct->Env->jinput->get('Itemid',0,'INT');

// -------------------- Table

$ct->getTable($params->get( 'establename' ), null);

if($ct->Table->tablename=='')
{
    $ct->app->enqueueMessage('Table not selected (185).', 'error');
    return false;
}

// --------------------- Filter
$ct->setFilter('', $ct->Env->menu_params->get('showpublished'));
$ct->Filter->addMenuParamFilter();

// --------------------- Sorting
$ct->Ordering->parseOrderByParam(true,$params,$ct->Env->Itemid);

// --------------------- Limit
$ct->applyLimits(true);


// --------------------- Layouts
$Layouts = new Layouts($ct);
		
if($params->get( 'ct_pagelayout' )!=null)
{
	$pagelayout=$Layouts->getLayout($params->get( 'ct_pagelayout' ));
	if($pagelayout=='')
		$pagelayout='{catalog:,notable}';
}
else
    $pagelayout='{catalog:,notable}';
	
if($params->get( 'ct_itemlayout' )!=null)
	$itemlayout=$Layouts->getLayout($params->get( 'ct_itemlayout' ));
else
	$itemlayout='';

// -------------------- Load Records
$ct->getRecords();

// -------------------- Parse Layouts
if ($ct->Env->legacysupport) {
    $catalogtablecode = JoomlaBasicMisc::generateRandomString();//this is temporary replace place holder. to not parse content result again

    $catalogtablecontent = tagProcessor_CatalogTableView::process($ct, $Layouts->layouttype, $pagelayout, $itemlayout, $catalogtablecode);
    if ($catalogtablecontent == '')
        $catalogtablecontent = tagProcessor_Catalog::process($ct, $Layouts->layouttype, $pagelayout, $itemlayout, $catalogtablecode);

    $LayoutProc = new LayoutProcessor($ct);
    $LayoutProc->layout = $pagelayout;
    $pagelayout = $LayoutProc->fillLayout(array(), null, '');
    $pagelayout = str_replace('&&&&quote&&&&', '"', $pagelayout); // search boxes may return HTMl elemnts that contain placeholders with quotes like this: &&&&quote&&&&
    $pagelayout = str_replace($catalogtablecode, $catalogtablecontent, $pagelayout);
}

if($params->get( 'allowcontentplugins' )==1)
	JoomlaBasicMisc::applyContentPlugins($pagelayout);
		
echo $pagelayout;
