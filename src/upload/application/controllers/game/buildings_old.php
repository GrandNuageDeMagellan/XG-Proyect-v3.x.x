<?php
/**
 * Buildings Controller
 *
 * PHP Version 5.5+
 *
 * @category Controller
 * @package  Application
 * @author   XG Proyect Team
 * @license  http://www.xgproyect.org XG Proyect
 * @link     http://www.xgproyect.org
 * @version  3.0.0
 */

namespace application\controllers\game;

use application\core\XGPCore;
use application\libraries\DevelopmentsLib;
use application\libraries\FormatLib;
use application\libraries\FunctionsLib;

/**
 * Buildings Class
 *
 * @category Classes
 * @package  Application
 * @author   XG Proyect Team
 * @license  http://www.xgproyect.org XG Proyect
 * @link     http://www.xgproyect.org
 * @version  3.0.0
 */
class Buildings extends XGPCore
{
    const MODULE_ID = 3;

    private $_current_user;
    private $_current_planet;
    private $_current_page;
    private $_lang;
    private $_objects;
    private $_allowed;

    /**
     * __construct()
     */
    public function __construct()
    {
        parent::__construct();

        // check if session is active
        parent::$users->checkSession();

        // Check module access
        FunctionsLib::moduleMessage(FunctionsLib::isModuleAccesible(self::MODULE_ID));

        $this->_current_user = parent::$users->getUserData();
        $this->_current_planet = parent::$users->getPlanetData();
        $this->_current_page = isset($_GET['page']) ? $_GET['page'] : null;
        $this->_lang = parent::$lang;
        $this->_objects = parent::$objects;

        if ($this->_current_page != 'resources' && $this->_current_page != 'station') {
            $this->_current_page = 'resources';
        }

        // check the current page and the allowed elements
        // resources page
        if ($this->_current_page == 'resources') {
            $this->_allowed['1'] = array(1, 2, 3, 4, 12, 22, 23, 24);
            $this->_allowed['3'] = array(12, 22, 23, 24);
        }

        // station page
        if ($this->_current_page == 'station') {
            $this->_allowed['1'] = array(14, 15, 21, 31, 33, 34, 44);
            $this->_allowed['3'] = array(14, 21, 41, 42, 43);
        }

        // build the page
        $this->buildPage();
    }

    /**
     * method __destruct
     * param
     * return close db connection
     */
    public function __destruct()
    {
        parent::$db->closeConnection();
    }

    /**
     * method build_page
     * param
     * return main method, loads everything
     */
    private function buildPage()
    {
        $resource = $this->_objects->getObjects();
        $parse = $this->_lang;
        $parse['dpath'] = DPATH;
        $page = '';
        $max_fields = DevelopmentsLib::maxFields($this->_current_planet);

        // time to do something
        $this->doCommand();

        DevelopmentsLib::setFirstElement($this->_current_planet, $this->_current_user);

        $Sprice = array();
        $queue = $this->showQueue($Sprice);

        $this->startBuilding();

        $full_queue = ( $queue['lenght'] < MAX_BUILDING_QUEUE_SIZE ) ? false : true;

        foreach ($this->_lang['tech'] as $building => $building_name) {
            if (in_array($building, $this->_allowed[$this->_current_planet['planet_type']])) {
                $queue['to_destroy'] = isset($queue['to_destroy']) ? $queue['to_destroy'] : 0;

                if ($this->_current_planet['planet_field_current'] < ( $max_fields - $queue['to_destroy'] )) {
                    $have_fields = true;
                } else {
                    $have_fields = false;
                }

                if (DevelopmentsLib::isDevelopmentAllowed($this->_current_user, $this->_current_planet, $building)) {
                    $building_level = $this->_current_planet[$resource[$building]];
                    $building_time  = DevelopmentsLib::developmentTime(
                        $this->_current_user,
                        $this->_current_planet,
                        $building,
                        $building_level
                    );

                    $parse['i']             = $building;
                    $parse['nivel']         = DevelopmentsLib::setLevelFormat($building_level);
                    $parse['n']             = $building_name;
                    $parse['descriptions']  = $this->_lang['res']['descriptions'][$building];
                    $parse['price']         = DevelopmentsLib::formatedDevelopmentPrice(
                        $this->_current_user,
                        $this->_current_planet,
                        $building,
                        true,
                        $building_level
                    );
                    $parse['time']          = DevelopmentsLib::formatedDevelopmentTime($building_time);
                    $parse['click']         = $this->setActionButton($have_fields, $full_queue, $building, $queue);

                    $page .= parent::$page->parseTemplate(parent::$page->getTemplate('buildings/buildings_builds_row'), $parse);
                }
            }
        }

        if ($queue['lenght'] > 0) {
            $parse['BuildListScript'] = DevelopmentsLib::currentBuilding($this->_current_page);
            $parse['BuildList'] = $queue['buildlist'];
        } else {
            $parse['BuildListScript'] = '';
            $parse['BuildList'] = '';
        }

        $parse['BuildingsList'] = $page;

        parent::$page->display(parent::$page->parseTemplate(parent::$page->getTemplate('buildings/buildings_builds'), $parse));
    }

    /**
     * method doCommand
     * param
     * return void
     */
    private function doCommand()
    {
        $cmd    = isset($_GET['cmd']) ? $_GET['cmd'] : null;

        if (!is_null($cmd)) {
            $building   = isset($_GET['building']) ? (int) $_GET['building'] : null;
            $list_id    = isset($_GET['listid']) ? (int) $_GET['listid'] : null;

            if ($this->canBuild($building, $list_id)) {
                switch ($cmd) {
                    case 'cancel':
                        $this->cancelCurrent();

                        break;

                    case 'remove':
                        $this->removeFromQueue($list_id);

                        break;

                    case 'insert':
                        $this->addToQueue($building, true);

                        break;

                    case 'destroy':
                        $this->addToQueue($building, false);

                        break;
                }
            }

            if (isset($_GET['r']) && $_GET['r'] == 'overview') {

                header('location:game.php?page=overview');
            } else {

                header('location:game.php?page=' . $this->_current_page);
            }
        }
    }

    /**
     * setActionButton
     * param $have_fields
     * param $full_queue
     * param $building
     * param $queue
     * return (string) $action
     */
    private function setActionButton($have_fields, $full_queue, $building, $queue)
    {
        // all field occupied
        $action = FormatLib::colorRed($this->_lang['bd_no_more_fields']);

        // with fields
        if ($have_fields && !$full_queue) {
            if ($queue['lenght'] == 0) {
                if (DevelopmentsLib::isDevelopmentPayable($this->_current_user, $this->_current_planet, $building, true, false) && !parent::$users->isOnVacations($this->_current_user)) {
                    $action = FunctionsLib::setUrl('game.php?page=' . $this->_current_page . '&cmd=insert&building=' . $building, '', FormatLib::colorGreen($this->_lang['bd_build']));
                } else {
                    $action = FormatLib::colorRed($this->_lang['bd_build']);
                }
            } else {
                $action = FunctionsLib::setUrl('game.php?page=' . $this->_current_page . '&cmd=insert&building=' . $building, '', FormatLib::colorGreen($this->_lang['bd_add_to_list']));
            }
        }

        if ($have_fields && $full_queue) {
            $action = FormatLib::colorRed($this->_lang['bd_build']);
        }

        if (( $building == 31 && DevelopmentsLib::isLabWorking($this->_current_user) ) or ( ( $building == 21 or $building == 14 or $building == 15 ) && DevelopmentsLib::isShipyardWorking($this->_current_planet) )) {
            $action = FormatLib::colorRed($this->_lang['bd_working']);
        }

        return $action;
    }

    /**
     * method canBuild
     * param (int) $building
     * param (int) $list_id
     * return (bool) $continue
     */
    private function canBuild($building, $list_id)
    {
        if (isset($building) && in_array($building, $this->_allowed[$this->_current_planet['planet_type']])) {
            $continue = true;
        } elseif (isset($list_id)) {
            $continue = true;
        }

        if ($building == 31 && $this->_current_user['research_current_research'] != 0) {
            $continue = false;
        }

        if (( $building == 21 or $building == 14 or $building == 15 ) && $this->_current_planet['planet_b_hangar'] != 0) {
            $continue = false;
        }

        return $continue;
    }

    /**
     * method startBuilding
     * param
     * return (void)
     */
    private function startBuilding()
    {
        parent::$db->query(
            "UPDATE " . PLANETS . " SET
            `planet_b_building_id` = '" . $this->_current_planet['planet_b_building_id'] . "',
            `planet_b_building` = '" . $this->_current_planet['planet_b_building'] . "'
            WHERE `planet_id` = '" . $this->_current_planet['planet_id'] . "';"
        );

        return;
    }

    /**
     * method cancelCurrent
     * param
     * return (bool) confirmation
     */
    private function cancelCurrent()
    {
        $CurrentQueue   = $this->_current_planet['planet_b_building_id'];

        if ($CurrentQueue != 0) {

            $QueueArray         = explode(";", $CurrentQueue);
            $ActualCount        = count($QueueArray);
            $CanceledIDArray    = explode(",", $QueueArray[0]);
            $building           = $CanceledIDArray[0];
            $BuildMode          = $CanceledIDArray[4];

            if ($ActualCount > 1) {

                array_shift($QueueArray);
                $NewCount       = count($QueueArray);
                $BuildEndTime   = time();

                for ($ID = 0; $ID < $NewCount; $ID++) {
                    $ListIDArray = explode(",", $QueueArray[$ID]);

                    if ($ListIDArray[0] == $building) {
                        $ListIDArray[1] -= 1;
                    }

                    $current_build_time = DevelopmentsLib::developmentTime($this->_current_user, $this->_current_planet, $ListIDArray[0]);
                    $BuildEndTime += $current_build_time;
                    $ListIDArray[2] = $current_build_time;
                    $ListIDArray[3] = $BuildEndTime;
                    $QueueArray[$ID] = implode(",", $ListIDArray);
                }
                $NewQueue = implode(";", $QueueArray);
                $ReturnValue = true;
                $BuildEndTime = '0';
            } else {
                $NewQueue = '0';
                $ReturnValue = false;
                $BuildEndTime = '0';
            }

            if ($BuildMode == 'destroy') {
                $ForDestroy = true;
            } else {
                $ForDestroy = false;
            }

            if ($building != false) {
                $Needed = DevelopmentsLib::developmentPrice($this->_current_user, $this->_current_planet, $building, true, $ForDestroy);
                $this->_current_planet['planet_metal'] += $Needed['metal'];
                $this->_current_planet['planet_crystal'] += $Needed['crystal'];
                $this->_current_planet['planet_deuterium'] += $Needed['deuterium'];
            }
        } else {
            $NewQueue = '0';
            $BuildEndTime = '0';
            $ReturnValue = false;
        }

        $this->_current_planet['planet_b_building_id'] = $NewQueue;
        $this->_current_planet['planet_b_building'] = $BuildEndTime;

        return $ReturnValue;
    }

    /**
     * method removeFromQueue
     * param $QueueID
     * return (int) the queue ID
     */
    private function removeFromQueue($QueueID)
    {
        if ($QueueID > 1) {
            $CurrentQueue = $this->_current_planet['planet_b_building_id'];

            if (!empty($CurrentQueue)) {
                $QueueArray = explode(";", $CurrentQueue);
                $ActualCount = count($QueueArray);
                if ($ActualCount < 2) {
                    FunctionsLib::redirect('game.php?page=' . $this->_current_page);
                }

                //  finding the buildings time
                $ListIDArrayToDelete = explode(",", $QueueArray[$QueueID - 1]);
                $lastB = $ListIDArrayToDelete;
                $lastID = $QueueID - 1;

                //search for biggest element
                for ($ID = $QueueID; $ID < $ActualCount; $ID++) {
                    //next buildings
                    $nextListIDArray = explode(",", $QueueArray[$ID]);
                    //if same type of element
                    if ($nextListIDArray[0] == $ListIDArrayToDelete[0]) {
                        $lastB = $nextListIDArray;
                        $lastID = $ID;
                    }
                }

                // update the rest of buildings queue
                for ($ID = $lastID; $ID < $ActualCount - 1; $ID++) {
                    $nextListIDArray = explode(",", $QueueArray[$ID + 1]);
                    $nextBuildEndTime = $nextListIDArray[3] - $lastB[2];
                    $nextListIDArray[3] = $nextBuildEndTime;
                    $QueueArray[$ID] = implode(",", $nextListIDArray);
                }

                unset($QueueArray[$ActualCount - 1]);
                $NewQueue = implode(";", $QueueArray);
            }

            $this->_current_planet['planet_b_building_id'] = $NewQueue;
        }

        return $QueueID;
    }

    /**
     * method addToQueue
     * param $building
     * param $AddMode
     * return (int) the queue ID
     */
    private function addToQueue($building, $AddMode = true)
    {
        $resource       = $this->_objects->getObjects();
        $CurrentQueue   = $this->_current_planet['planet_b_building_id'];
        $queue          = $this->showQueue();
        $max_fields     = DevelopmentsLib::maxFields($this->_current_planet);
        $QueueArray     = [];

        if ($AddMode) {
            if (($this->_current_planet['planet_field_current'] >= ($max_fields - $queue['lenght']))) {
                FunctionsLib::redirect('game.php?page=' . $this->_current_page);
            }
        }

        if ($CurrentQueue != 0) {
            $QueueArray = explode(";", $CurrentQueue);
            $ActualCount = count($QueueArray);
        } else {
            $QueueArray = "";
            $ActualCount = 0;
        }

        if ($AddMode == true) {
            $BuildMode = 'build';
        } else {
            $BuildMode = 'destroy';
        }

        if ($ActualCount < MAX_BUILDING_QUEUE_SIZE) {
            $QueueID = $ActualCount + 1;
        } else {
            $QueueID = false;
        }

        $continue = false;

        if ($QueueID != false && DevelopmentsLib::isDevelopmentAllowed($this->_current_user, $this->_current_planet, $building)) {
            if ($QueueID <= 1) {
                if (DevelopmentsLib::isDevelopmentPayable($this->_current_user, $this->_current_planet, $building, true, false) && !parent::$users->isOnVacations($this->_current_user)) {
                    $continue = true;
                }
            } else {
                $continue = true;
            }

            if ($continue) {
                if ($QueueID > 1) {
                    $InArray = 0;
                    for ($QueueElement = 0; $QueueElement < $ActualCount; $QueueElement++) {
                        $QueueSubArray = explode(",", $QueueArray[$QueueElement]);
                        if ($QueueSubArray[0] == $building) {
                            $InArray++;
                        }
                    }
                } else {
                    $InArray = 0;
                }

                if ($InArray != 0) {
                    $ActualLevel = $this->_current_planet[$resource[$building]];
                    if ($AddMode == true) {
                        $BuildLevel = $ActualLevel + 1 + $InArray;
                        $this->_current_planet[$resource[$building]] += $InArray;
                        $BuildTime = DevelopmentsLib::developmentTime($this->_current_user, $this->_current_planet, $building);
                        $this->_current_planet[$resource[$building]] -= $InArray;
                    } else {
                        $BuildLevel = $ActualLevel - 1 - $InArray;
                        $this->_current_planet[$resource[$building]] -= $InArray;
                        $BuildTime = DevelopmentsLib::developmentTime($this->_current_user, $this->_current_planet, $building) / 2;
                        $this->_current_planet[$resource[$building]] += $InArray;
                    }
                } else {
                    $ActualLevel = $this->_current_planet[$resource[$building]];
                    if ($AddMode == true) {
                        $BuildLevel = $ActualLevel + 1;
                        $BuildTime = DevelopmentsLib::developmentTime($this->_current_user, $this->_current_planet, $building);
                    } else {
                        $BuildLevel = $ActualLevel - 1;
                        $BuildTime = DevelopmentsLib::developmentTime($this->_current_user, $this->_current_planet, $building) / 2;
                    }
                }

                if ($QueueID == 1) {
                    $QueueArray     = [];
                    $BuildEndTime   = time() + $BuildTime;
                } else {
                    $PrevBuild = explode(",", $QueueArray[$ActualCount - 1]);
                    $BuildEndTime = $PrevBuild[3] + $BuildTime;
                }

                $QueueArray[$ActualCount] = $building . "," . $BuildLevel . "," . $BuildTime . "," . $BuildEndTime . "," . $BuildMode;
                $NewQueue = implode(";", $QueueArray);

                $this->_current_planet['planet_b_building_id'] = $NewQueue;
            }
        }
        return $QueueID;
    }

    /**
     * method showQueue
     * param $Sprice
     * return (array) the queue to build data
     */
    private function showQueue(&$Sprice = false)
    {
        $lang = $this->_lang;
        $CurrentQueue = $this->_current_planet['planet_b_building_id'];
        $QueueID = 0;
        $to_destroy = 0;
        $BuildMode = '';

        if ($CurrentQueue != 0) {
            $QueueArray = explode(";", $CurrentQueue);
            $ActualCount = count($QueueArray);
        } else {
            $QueueArray = '0';
            $ActualCount = 0;
        }

        $ListIDRow = '';

        if ($ActualCount != 0) {
            $PlanetID = $this->_current_planet['planet_id'];
            for ($QueueID = 0; $QueueID < $ActualCount; $QueueID++) {
                $BuildArray = explode(",", $QueueArray[$QueueID]);
                $BuildEndTime = floor($BuildArray[3]);
                $CurrentTime = floor(time());

                if ($BuildMode == 'destroy') {
                    $to_destroy++;
                }

                if ($BuildEndTime >= $CurrentTime) {
                    $ListID = $QueueID + 1;
                    $building = $BuildArray[0];
                    $BuildLevel = $BuildArray[1];
                    $BuildMode = $BuildArray[4];
                    $BuildTime = $BuildEndTime - time();
                    $ElementTitle = $this->_lang['tech'][$building];

                    if (isset($Sprice[$building]) && $Sprice !== false && $BuildLevel > $Sprice[$building]) {
                        $Sprice[$building] = $BuildLevel;
                    }

                    if ($ListID > 0) {
                        $ListIDRow .= "<tr>";
                        if ($BuildMode == 'build') {
                            $ListIDRow .= "	<td class=\"l\" colspan=\"2\">" . $ListID . ".: " . $ElementTitle . " " . $BuildLevel . "</td>";
                        } else {
                            $ListIDRow .= "	<td class=\"l\" colspan=\"2\">" . $ListID . ".: " . $ElementTitle . " " . $BuildLevel . " " . $this->_lang['bd_dismantle'] . "</td>";
                        }
                        $ListIDRow .= "	<td class=\"k\">";

                        if ($ListID == 1) {
                            $ListIDRow .= "		<div id=\"blc\" class=\"z\">" . $BuildTime . "<br>";
                            $ListIDRow .= "		<a href=\"game.php?page=" . $this->_current_page . "&listid=" . $ListID . "&amp;cmd=cancel&amp;planet=" . $PlanetID . "\">" . $this->_lang['bd_interrupt'] . "</a></div>";
                            $ListIDRow .= "		<script language=\"JavaScript\">";
                            $ListIDRow .= "			pp = \"" . $BuildTime . "\";\n";
                            $ListIDRow .= "			pk = \"" . $ListID . "\";\n";
                            $ListIDRow .= "			pm = \"cancel\";\n";
                            $ListIDRow .= "			pl = \"" . $PlanetID . "\";\n";
                            $ListIDRow .= "			t();\n";
                            $ListIDRow .= "		</script>";
                            $ListIDRow .= "		<strong color=\"lime\"><br><font color=\"lime\">" . date(FunctionsLib::readConfig('date_format_extended'), $BuildEndTime) . "</font></strong>";
                        } else {
                            $ListIDRow .= "		<font color=\"red\">";
                            $ListIDRow .= "		<a href=\"game.php?page=" . $this->_current_page . "&listid=" . $ListID . "&amp;cmd=remove&amp;planet=" . $PlanetID . "\">" . $this->_lang['bd_cancel'] . "</a></font>";
                        }

                        $ListIDRow .= "	</td>";
                        $ListIDRow .= "</tr>";
                    }
                }
            }
        }

        $RetValue['to_destoy'] = $to_destroy;
        $RetValue['lenght'] = $ActualCount;
        $RetValue['buildlist'] = $ListIDRow;

        return $RetValue;
    }
}

/* end of buildings.php */
