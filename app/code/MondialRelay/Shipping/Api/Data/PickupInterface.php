<?php
/**
 * Mondial Relay Shipping Module
 *
 * @author    Magentix
 * @copyright Copyright © 2017 Magentix. All rights reserved.
 * @license   https://www.magentix.fr/en/licence.html Magentix Software Licence
 * @link      https://mondialrelay.magentix.fr/
 */
namespace MondialRelay\Shipping\Api\Data;

/**
 * Interface PickupInterface
 */
interface PickupInterface
{
    /**
     * @return string
     */
    public function getCode();

    /**
     * @return string
     */
    public function getStat();

    /**
     * @return string
     */
    public function getNum();

    /**
     * @return string
     */
    public function getLgadr1();

    /**
     * @return string
     */
    public function getLgadr2();

    /**
     * @return string
     */
    public function getLgadr3();

    /**
     * @return string
     */
    public function getLgadr4();

    /**
     * @return string
     */
    public function getCp();

    /**
     * @return string
     */
    public function getVille();

    /**
     * @return string
     */
    public function getPays();

    /**
     * @return string
     */
    public function getLocalisation1();

    /**
     * @return string
     */
    public function getLocalisation2();

    /**
     * @return string
     */
    public function getLatitude();

    /**
     * @return string
     */
    public function getLongitude();

    /**
     * @return string
     */
    public function getTypeactivite();

    /**
     * @return string
     */
    public function getNace();

    /**
     * @return string
     */
    public function getInformation();

    /**
     * @return string|null
     */
    public function getHorairesLundi();

    /**
     * @return string|null
     */
    public function getHorairesMardi();

    /**
     * @return string|null
     */
    public function getHorairesMercredi();

    /**
     * @return string|null
     */
    public function getHorairesJeudi();

    /**
     * @return string|null
     */
    public function getHorairesVendredi();

    /**
     * @return string|null
     */
    public function getHorairesSamedi();

    /**
     * @return string|null
     */
    public function getHorairesDimanche();

    /**
     * @return string[]
     */
    public function getInformationsDispo();

    /**
     * @return string
     */
    public function getDebut();

    /**
     * @return string
     */
    public function getFin();

    /**
     * @return string
     */
    public function getUrlPhoto();

    /**
     * @return string
     */
    public function getUrlPlan();

    /**
     * @return string
     */
    public function getDistance();
}
