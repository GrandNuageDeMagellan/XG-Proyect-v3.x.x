<?php
/**
 * Colonize Library
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
use application\libraries\FunctionsLib;
use application\libraries\PlanetLib;
use application\libraries\Statistics_library;

/**
 * Colonize Class
 *
 * @category Classes
 * @package  Application
 * @author   XG Proyect Team
 * @license  http://www.xgproyect.org XG Proyect
 * @link     http://www.xgproyect.org
 * @version  3.0.0
 */
class Colonize extends Missions
{

    /**
     * __construct()
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
    public function colonizeMission($fleet_row)
    {
        if ($fleet_row['fleet_mess'] == 0) {
            $colonization_check = $this->Missions_Model->getPlanetAndUserCountsCounts([
                'user_id' => $fleet_row['fleet_owner'],
                'coords' => [
                    'galaxy' => $fleet_row['fleet_end_galaxy'],
                    'system' => $fleet_row['fleet_end_system'],
                    'planet' => $fleet_row['fleet_end_planet'],
                ],
            ]);

            // SOME REQUIRED VALUES
            $target_coords = sprintf($this->langs['sys_adress_planet'], $fleet_row['fleet_end_galaxy'], $fleet_row['fleet_end_system'], $fleet_row['fleet_end_planet']);
            $max_colonies = FleetsLib::getMaxColonies($colonization_check['astro_level']);
            $planet_count = $colonization_check['planet_count'] - 1; // THE TOTAL AMOUNT OF PLANETS MINUS 1 (BECAUSE THE MAIN PLANET IT'S NOT CONSIDERED)
            // DIFFERENT TYPES OF MESSAGES
            $message[1] = $this->langs['sys_colo_arrival'] . $target_coords . $this->langs['sys_colo_maxcolo'] . ($max_colonies + 1) . $this->langs['sys_colo_planet'];
            $message[2] = $this->langs['sys_colo_arrival'] . $target_coords . $this->langs['sys_colo_allisok'];
            $message[3] = $this->langs['sys_colo_arrival'] . $target_coords . $this->langs['sys_colo_notfree'];
            $message[4] = $this->langs['sys_colo_arrival'] . $target_coords . $this->langs['sys_colo_astro_level'];

            if ($colonization_check['galaxy_count'] == 0) {
                if ($planet_count >= $max_colonies) {
                    $this->colonize_message($fleet_row['fleet_owner'], $message[1], $fleet_row['fleet_start_time']);

                    parent::returnFleet($fleet_row['fleet_id']);
                } elseif (!$this->position_allowed($fleet_row['fleet_end_planet'], $colonization_check['astro_level'])) {
                    $this->colonize_message($fleet_row['fleet_owner'], $message[4], $fleet_row['fleet_start_time']);

                    parent::returnFleet($fleet_row['fleet_id']);
                } else {
                    if ($this->start_creation($fleet_row)) {
                        $this->colonize_message($fleet_row['fleet_owner'], $message[2], $fleet_row['fleet_start_time']);

                        if ($fleet_row['fleet_amount'] == 1) {
                            $this->Missions_Model->updateColonizationStatistics([
                                'points' => Statistics_library::calculatePoints(208, 1),
                                'coords' => [
                                    'galaxy' => $fleet_row['fleet_start_galaxy'],
                                    'system' => $fleet_row['fleet_start_system'],
                                    'planet' => $fleet_row['fleet_start_planet'],
                                    'type' => $fleet_row['fleet_start_type'],
                                ],
                            ]);
                            parent::storeResources($fleet_row);
                            parent::removeFleet($fleet_row['fleet_id']);
                        } else {
                            parent::storeResources($fleet_row);

                            $this->Missions_Model->updateColonizatonReturningFleet([
                                'ships' => $this->build_new_fleet($fleet_row['fleet_array']),
                                'points' => Statistics_library::calculatePoints(208, 1),
                                'fleet_id' => $fleet_row['fleet_id'],
                                'coords' => [
                                    'galaxy' => $fleet_row['fleet_start_galaxy'],
                                    'system' => $fleet_row['fleet_start_system'],
                                    'planet' => $fleet_row['fleet_start_planet'],
                                    'type' => $fleet_row['fleet_start_type'],
                                ],
                            ]);
                        }
                    } else {
                        $this->colonize_message($fleet_row['fleet_owner'], $message[3], $fleet_row['fleet_end_time']);

                        parent::returnFleet($fleet_row['fleet_id']);
                    }
                }
            } else {
                $this->colonize_message($fleet_row['fleet_owner'], $message[3], $fleet_row['fleet_end_time']);

                parent::returnFleet($fleet_row['fleet_id']);
            }
        }

        if ($fleet_row['fleet_end_time'] < time()) {
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
    private function start_creation($fleet_row)
    {
        $creator = new PlanetLib();

        return $creator->setNewPlanet($fleet_row['fleet_end_galaxy'], $fleet_row['fleet_end_system'], $fleet_row['fleet_end_planet'], $fleet_row['fleet_owner'], $this->langs['sys_colo_defaultname'], false);
    }

    /**
     * bbCode function.
     *
     * @param string $string String
     *
     * @return void
     */
    private function build_new_fleet($fleet_array)
    {
        $current_fleet = FleetsLib::getFleetShipsArray($fleet_array);
        $new_fleet = [];

        foreach ($current_fleet as $ship => $count) {

            if ($ship == 208) {

                if ($count > 1) {
                    $new_fleet[$ship] = ($count - 1);
                }
            } else {

                if ($count != 0) {
                    $new_fleet[$ship] = $count;
                }
            }
        }

        return FleetsLib::setFleetShipsArray($new_fleet);
    }

    /**
     * bbCode function.
     *
     * @param string $string String
     *
     * @return void
     */
    private function colonize_message($owner, $message, $time)
    {
        FunctionsLib::sendMessage($owner, '', $time, 5, $this->langs['sys_colo_mess_from'], $this->langs['sys_colo_mess_report'], $message);
    }

    /**
     * bbCode function.
     *
     * @param string $string String
     *
     * @return void
     */
    private function position_allowed($position, $level)
    {
        // CHECK IF THE POSITION IS NEAR THE SPACE LIMITS
        if ($position <= 3 or $position >= 13) {
            // POSITIONS 3 AND 13 CAN BE POPULATED FROM LEVEL 4 ONWARDS.
            if ($level >= 4 && ($position == 3 or $position == 13)) {
                return true;
            }

            // POSITIONS 2 AND 14 CAN BE POPULATED FROM LEVEL 6 ONWARDS.
            if ($level >= 6 && ($position == 2 or $position == 14)) {
                return true;
            }

            // POSITIONS 1 AND 15 CAN BE POPULATED FROM LEVEL 8 ONWARDS.
            if ($level >= 8) {
                return true;
            }

            return false;
        } else {
            return true;
        }
    }
}

/* end of colonize.php */
