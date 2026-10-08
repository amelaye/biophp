<?php
/**
 * Maps VcfVariant value objects to and from their Doctrine records
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 October 2026
 * Last modified 8 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Variants\Service;

use Amelaye\BioPHP\Domain\Variants\Entity\VcfVariantRecord;
use Amelaye\BioPHP\Domain\Variants\Interfaces\VcfVariantRecordMapperInterface;
use Amelaye\BioPHP\Domain\Variants\ValueObject\VcfVariant;

/**
 * Class VcfVariantRecordMapper
 * @package Amelaye\BioPHP\Domain\Variants\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class VcfVariantRecordMapper implements VcfVariantRecordMapperInterface
{
    /**
     * @param   VcfVariant          $oVariant
     * @return  VcfVariantRecord
     */
    public function toRecord(VcfVariant $oVariant): VcfVariantRecord
    {
        $oRecord = new VcfVariantRecord();
        $oRecord->setChrom($oVariant->getChrom())
            ->setPosition($oVariant->getPosition())
            ->setVariantId($oVariant->getId())
            ->setReference($oVariant->getReference())
            ->setAlternates($oVariant->getAlternates())
            ->setQuality($oVariant->getQuality())
            ->setFilter($oVariant->getFilter())
            ->setInfo($oVariant->getInfo());

        return $oRecord;
    }

    /**
     * A record stored before ALT alleles were validated may hold the empty allele an empty ALT
     * column used to give : it is no allele, and is dropped rather than making the record unreadable.
     * @param   VcfVariantRecord    $oRecord
     * @return  VcfVariant
     */
    public function toVariant(VcfVariantRecord $oRecord): VcfVariant
    {
        return new VcfVariant(
            $oRecord->getChrom(),
            $oRecord->getPosition(),
            $oRecord->getVariantId(),
            $oRecord->getReference(),
            array_values(array_filter($oRecord->getAlternates(), fn($sAlternate) => $sAlternate !== "")),
            $oRecord->getQuality(),
            $oRecord->getFilter(),
            $oRecord->getInfo()
        );
    }
}
