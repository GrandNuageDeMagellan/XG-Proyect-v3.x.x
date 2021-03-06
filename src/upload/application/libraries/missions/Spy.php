<?php
/**
 * Spy Library
 *
 * PHP Version 7.1+
 *
 * @category Library
 * @package  Application
 * @author   XG Proyect Team
 * @license  http://www.xgproyect.org XG Proyect
 * @link     http://www.xgproyect.org
 * @version  3.0.0
 */
namespace application\libraries\missions;

use application\libraries\FleetsLib;
use application\libraries\FormatLib;
use application\libraries\FunctionsLib;
use application\libraries\OfficiersLib;
use application\libraries\TimingLibrary as Timing;

/**
 * Spy Class
 *
 * @category Classes
 * @package  Application
 * @author   XG Proyect Team
 * @license  http://www.xgproyect.org XG Proyect
 * @link     http://www.xgproyect.org
 * @version  3.0.0
 */
class Spy extends Missions
{

    /**
     * bbCode function.
     *
     * @param string $string String
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * bbCode function.
     *
     * @param string $string String
     *
     * @return void
     */
    public function spyMission($fleet_row)
    {
        if ($fleet_row['fleet_mess'] == 0 && $fleet_row['fleet_start_time'] <= time()) {

            $current_data = $this->Missions_Model->getSpyUserDataByCords([
                'coords' => [
                    'galaxy' => $fleet_row['fleet_start_galaxy'],
                    'system' => $fleet_row['fleet_start_system'],
                    'planet' => $fleet_row['fleet_start_planet'],
                    'type' => $fleet_row['fleet_start_type'],
                ],
            ]);

            $target_data = $this->Missions_Model->getInquiredUserDataByCords([
                'coords' => [
                    'galaxy' => $fleet_row['fleet_end_galaxy'],
                    'system' => $fleet_row['fleet_end_system'],
                    'planet' => $fleet_row['fleet_end_planet'],
                    'type' => $fleet_row['fleet_end_type'],
                ],
            ]);

            $CurrentSpyLvl = OfficiersLib::getMaxEspionage($current_data['research_espionage_technology'], $current_data['premium_officier_technocrat']);
            $TargetSpyLvl = OfficiersLib::getMaxEspionage($target_data['research_espionage_technology'], $target_data['premium_officier_technocrat']);
            $fleet = FleetsLib::getFleetShipsArray($fleet_row['fleet_array']);

            parent::makeUpdate($fleet_row['fleet_end_galaxy'], $fleet_row['fleet_end_system'], $fleet_row['fleet_end_planet'], $fleet_row['fleet_end_type']);

            foreach ($fleet as $id => $amount) {

                if ($id == "210") {
                    $LS = $amount;
                    $SpyToolDebris = $LS * 300;

                    $MaterialsInfo = $this->spy_target($target_data, 0, $this->langs['sys_spy_maretials']);
                    $Materials = $MaterialsInfo['String'];

                    $PlanetFleetInfo = $this->spy_target($target_data, 1, $this->langs['sys_spy_fleet']);
                    $PlanetFleet = $Materials;
                    $PlanetFleet .= $PlanetFleetInfo['String'];

                    $PlanetDefenInfo = $this->spy_target($target_data, 2, $this->langs['sys_spy_defenses']);
                    $PlanetDefense = $PlanetFleet;
                    $PlanetDefense .= $PlanetDefenInfo['String'];

                    $PlanetBuildInfo = $this->spy_target($target_data, 3, $this->langs['tech'][0]);
                    $PlanetBuildings = $PlanetDefense;
                    $PlanetBuildings .= $PlanetBuildInfo['String'];

                    $TargetTechnInfo = $this->spy_target($target_data, 4, $this->langs['tech'][100]);
                    $TargetTechnos = $PlanetBuildings;
                    $TargetTechnos .= $TargetTechnInfo['String'];

                    $TargetForce = ($PlanetFleetInfo['Count'] * $LS) / 4;

                    if ($TargetForce > 100) {
                        $TargetForce = 100;
                    }

                    $TargetChances = mt_rand(0, $TargetForce);
                    $SpyerChances = mt_rand(0, 100);

                    if ($TargetChances >= $SpyerChances) {
                        $DestProba = "<font color=\"red\">" . $this->langs['sys_mess_spy_destroyed'] . "</font>";
                    } elseif ($TargetChances < $SpyerChances) {
                        $DestProba = sprintf($this->langs['sys_mess_spy_lostproba'], $TargetChances);
                    }

                    $AttackLink = "<center>";
                    $AttackLink .= "<a href=\"game.php?page=fleet1&galaxy=" . $fleet_row['fleet_end_galaxy'] . "&system=" . $fleet_row['fleet_end_system'] . "";
                    $AttackLink .= "&planet=" . $fleet_row['fleet_end_planet'] . "&planettype=" . $fleet_row['fleet_end_type'] . "";
                    $AttackLink .= "&target_mission=1";
                    $AttackLink .= " \">" . $this->langs['type_mission'][1] . "";
                    $AttackLink .= "</a></center>";
                    $MessageEnd = "<center>" . $DestProba . "</center>";

                    $spionage_difference = abs($CurrentSpyLvl - $TargetSpyLvl);

                    if ($TargetSpyLvl >= $CurrentSpyLvl) {
                        $ST = pow($spionage_difference, 2);
                        $resources = 1;
                        $fleet = $ST + 2;
                        $defense = $ST + 3;
                        $buildings = $ST + 5;
                        $tech = $ST + 7;
                    }

                    if ($CurrentSpyLvl > $TargetSpyLvl) {
                        $ST = pow($spionage_difference, 2) * -1;
                        $resources = 1;
                        $fleet = $ST + 2;
                        $defense = $ST + 3;
                        $buildings = $ST + 5;
                        $tech = $ST + 7;
                    }

                    if ($resources <= $LS) {
                        $SpyMessage = $Materials . "<br />" . $AttackLink . $MessageEnd;
                    }

                    if ($fleet <= $LS) {
                        $SpyMessage = $PlanetFleet . "<br />" . $AttackLink . $MessageEnd;
                    }

                    if ($defense <= $LS) {
                        $SpyMessage = $PlanetDefense . "<br />" . $AttackLink . $MessageEnd;
                    }

                    if ($buildings <= $LS) {
                        $SpyMessage = $PlanetBuildings . "<br />" . $AttackLink . $MessageEnd;
                    }

                    if ($tech <= $LS) {
                        $SpyMessage = $TargetTechnos . "<br />" . $AttackLink . $MessageEnd;
                    }

                    FunctionsLib::sendMessage($fleet_row['fleet_owner'], '', $fleet_row['fleet_start_time'], 0, $this->langs['sys_mess_qg'], $this->langs['sys_mess_spy_report'], $SpyMessage, true);

                    $TargetMessage = $this->langs['sys_mess_spy_ennemyfleet'] . " " . $current_data['planet_name'];
                    $TargetMessage .= " <a href=\"game.php?page=galaxy&mode=3&galaxy=" . $current_data['planet_galaxy'] . "&system=" . $current_data['planet_system'] . "\">";
                    $TargetMessage .= "[" . $current_data['planet_galaxy'] . ":" . $current_data['planet_system'] . ":" . $current_data['planet_planet'] . "]</a> (" . $current_data['user_name'] . ") ";
                    $TargetMessage .= $this->langs['sys_mess_spy_seen_at'] . " " . $target_data['planet_name'];
                    $TargetMessage .= " <a href=\"game.php?page=galaxy&mode=3&galaxy=" . $target_data['planet_galaxy'] . "&system=" . $target_data['planet_system'] . "\">";
                    $TargetMessage .= "[" . $target_data['planet_galaxy'] . ":" . $target_data['planet_system'] . ":" . $target_data['planet_planet'] . "]</a>.";

                    FunctionsLib::sendMessage($target_data['planet_user_id'], '', $fleet_row['fleet_start_time'], 0, $this->langs['sys_mess_spy_control'], $this->langs['sys_mess_spy_activity'], $TargetMessage . ' ' . sprintf($this->langs['sys_mess_spy_lostproba'], $TargetChances), true);

                    if ($TargetChances >= $SpyerChances) {

                        $this->Missions_Model->updateCrystalDebrisByPlanetId([
                            'time' => time(),
                            'crystal' => (0 + $SpyToolDebris),
                            'planet_id' => $target_data['planet_id'],
                        ]);

                        parent::removeFleet($fleet_row['fleet_id']);
                    } else {
                        parent::returnFleet($fleet_row['fleet_id']);
                    }
                }
            }
        } elseif ($fleet_row['fleet_mess'] == 1 && $fleet_row['fleet_end_time'] <= time()) {
            parent::restoreFleet($fleet_row, true);
            parent::removeFleet($fleet_row['fleet_id']);
        }
    }

    /**
     * bbCode function.
     *
     * @param string $string String
     *
     * @return void
     */
    private function spy_target($target_data, $mode, $TitleString)
    {
        $LookAtLoop = true;
        $Count = 0;

        switch ($mode) {
            case 0:

                $String = "<table width=\"440\"><tr><td class=\"c\" colspan=\"5\">";
                $String .= $TitleString . " " . $target_data['planet_name'];
                $String .= " <a href=\"game.php?page=galaxy&mode=3&galaxy=" . $target_data['planet_galaxy'] . "&system=" . $target_data['planet_system'] . "\">";
                $String .= "[" . $target_data['planet_galaxy'] . ":" . $target_data['planet_system'] . ":" . $target_data['planet_planet'] . "]</a>";
                $String .= $this->langs['sys_the'] . Timing::formatExtendedDate(time()) . "</td>";
                $String .= "</tr><tr>";
                $String .= "<td width=220>" . $this->langs['Metal'] . "</td><td width=220 align=right>" . FormatLib::prettyNumber($target_data['planet_metal']) . "</td><td>&nbsp;</td>";
                $String .= "<td width=220>" . $this->langs['Crystal'] . "</td></td><td width=220 align=right>" . FormatLib::prettyNumber($target_data['planet_crystal']) . "</td>";
                $String .= "</tr><tr>";
                $String .= "<td width=220>" . $this->langs['Deuterium'] . "</td><td width=220 align=right>" . FormatLib::prettyNumber($target_data['planet_deuterium']) . "</td><td>&nbsp;</td>";
                $String .= "<td width=220>" . $this->langs['Energy'] . "</td><td width=220 align=right>" . FormatLib::prettyNumber($target_data['planet_energy_max']) . "</td>";
                $String .= "</tr>";

                $LookAtLoop = false;

                break;

            case 1:

                $ResFrom[0] = 200;
                $ResTo[0] = 299;
                $Loops = 1;

                break;

            case 2:

                $ResFrom[0] = 400;
                $ResTo[0] = 499;
                $ResFrom[1] = 500;
                $ResTo[1] = 599;
                $Loops = 2;

                break;

            case 3:

                $ResFrom[0] = 1;
                $ResTo[0] = 99;
                $Loops = 1;

                break;

            case 4:

                $ResFrom[0] = 100;
                $ResTo[0] = 199;
                $Loops = 1;

                break;
        }

        if ($LookAtLoop == true) {
            $String = "<table width=\"440\" cellspacing=\"1\"><tr><td class=\"c\" colspan=\"" . ((2 * 2) + (2 - 1)) . "\">" . $TitleString . "</td></tr>";
            $Count = 0;
            $CurrentLook = 0;

            while ($CurrentLook < $Loops) {
                $row = 0;
                for ($Item = $ResFrom[$CurrentLook]; $Item <= $ResTo[$CurrentLook]; $Item++) {
                    if (isset($this->resource[$Item]) && $target_data[$this->resource[$Item]] > 0) {
                        if ($row == 0) {
                            $String .= "<tr>";
                        }

                        $String .= "<td align=left>" . $this->langs['tech'][$Item] . "</td><td align=right>" . $target_data[$this->resource[$Item]] . "</td>";

                        if ($row < 2 - 1) {
                            $String .= "<td>&nbsp;</td>";
                        }

                        $Count += $target_data[$this->resource[$Item]];
                        $row++;

                        if ($row == 2) {
                            $String .= "</tr>";
                            $row = 0;
                        }
                    }
                }

                while ($row != 0) {
                    $String .= "<td>&nbsp;</td><td>&nbsp;</td>";
                    $row++;

                    if ($row == 2) {
                        $String .= "</tr>";
                        $row = 0;
                    }
                }
                $CurrentLook++;
            }
        }

        $String .= "</table>";

        $return['String'] = $String;
        $return['Count'] = $Count;

        return $return;
    }
}

/* end of spy.php */
