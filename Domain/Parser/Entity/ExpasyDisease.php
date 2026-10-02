<?php
/**
 * One disease association (DI field) from an ExPASy ENZYME entry
 * Freely inspired by BioPHP's project biophp.org
 * Created 12 August 2026
 * Last modified 2 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Parser\Entity;

use Amelaye\BioPHP\Domain\Parser\Interfaces\ExpasyDiseaseInterface;

/**
 * Class ExpasyDisease
 * @package Amelaye\BioPHP\Domain\Parser\Entity
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class ExpasyDisease implements ExpasyDiseaseInterface
{
    /**
     * @var string
     */
    private string $disease = "";

    /**
     * @var string
     */
    private string $reference = "";

    /**
     * @return string
     */
    public function getDisease(): string
    {
        return $this->disease;
    }

    /**
     * @param string $disease
     */
    public function setDisease(string $disease): void
    {
        $this->disease = $disease;
    }

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }
}
