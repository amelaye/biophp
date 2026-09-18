<?php
/**
 * Contract for one disease association (DI field) from an ExPASy ENZYME entry
 * Freely inspired by BioPHP's project biophp.org
 * Created 18 September 2026
 * Last modified 18 September 2026
 */
namespace Amelaye\BioPHP\Domain\Parser\Interfaces;

/**
 * Interface ExpasyDiseaseInterface
 * @package Amelaye\BioPHP\Domain\Parser\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface ExpasyDiseaseInterface
{
    /**
     * @return string
     */
    public function getDisease(): string;

    /**
     * @param string $disease
     */
    public function setDisease(string $disease): void;

    /**
     * @return string
     */
    public function getReference(): string;

    /**
     * @param string $reference
     */
    public function setReference(string $reference): void;
}
