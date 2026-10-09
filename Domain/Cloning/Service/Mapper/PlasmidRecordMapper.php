<?php
/**
 * Maps Plasmid aggregates to and from their Doctrine records
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 October 2026
 * Last modified 6 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Cloning\Service\Mapper;

use Amelaye\BioPHP\Domain\Cloning\Aggregate\Plasmid;
use Amelaye\BioPHP\Domain\Cloning\Entity\PlasmidFeatureRecord;
use Amelaye\BioPHP\Domain\Cloning\Entity\PlasmidRecord;
use Amelaye\BioPHP\Domain\Cloning\Interfaces\PlasmidRecordMapperInterface;
use Amelaye\BioPHP\Domain\Cloning\ValueObject\PlasmidFeature;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\CircularDnaSequence;

/**
 * Rebuilding a Plasmid goes back through its constructors, so every invariant (alphabet, feature
 * type, strand, colour, coordinates within the sequence length) is checked again on the way out of
 * the database. Features come back in their stored position order.
 * Class PlasmidRecordMapper
 * @package Amelaye\BioPHP\Domain\Cloning\Service\Mapper
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class PlasmidRecordMapper implements PlasmidRecordMapperInterface
{
    /**
     * @param   Plasmid         $oPlasmid
     * @return  PlasmidRecord
     */
    public function toRecord(Plasmid $oPlasmid): PlasmidRecord
    {
        $oRecord = new PlasmidRecord();
        $oRecord->setName($oPlasmid->getName())
            ->setSequence($oPlasmid->getSequence()->getValue())
            ->setDescription($oPlasmid->getDescription())
            ->setExternalId($oPlasmid->getExternalId())
            ->setMetadata($oPlasmid->getMetadata());

        foreach ($oPlasmid->getFeatures() as $iPosition => $oFeature) {
            $oFeatureRecord = new PlasmidFeatureRecord();
            $oFeatureRecord->setPosition($iPosition)
                ->setName($oFeature->getName())
                ->setType($oFeature->getType())
                ->setStartPosition($oFeature->getStart())
                ->setEndPosition($oFeature->getEnd())
                ->setStrand($oFeature->getStrand())
                ->setColor($oFeature->getColor())
                ->setNote($oFeature->getNote())
                ->setExternalId($oFeature->getExternalId())
                ->setMetadata($oFeature->getMetadata())
                ->setPhase($oFeature->getPhase());

            $oRecord->addFeature($oFeatureRecord);
        }

        return $oRecord;
    }

    /**
     * @param   PlasmidRecord   $oRecord
     * @return  Plasmid
     */
    public function toPlasmid(PlasmidRecord $oRecord): Plasmid
    {
        $aFeatureRecords = $oRecord->getFeatures()->toArray();
        usort(
            $aFeatureRecords,
            function (PlasmidFeatureRecord $oLeft, PlasmidFeatureRecord $oRight) {
                return $oLeft->getPosition() <=> $oRight->getPosition();
            }
        );

        $aFeatures = array_map(
            function (PlasmidFeatureRecord $oFeatureRecord) {
                return new PlasmidFeature(
                    $oFeatureRecord->getName(),
                    $oFeatureRecord->getType(),
                    $oFeatureRecord->getStartPosition(),
                    $oFeatureRecord->getEndPosition(),
                    $oFeatureRecord->getStrand(),
                    $oFeatureRecord->getColor(),
                    $oFeatureRecord->getNote(),
                    $oFeatureRecord->getExternalId(),
                    $oFeatureRecord->getMetadata(),
                    $oFeatureRecord->getPhase()
                );
            },
            $aFeatureRecords
        );

        return new Plasmid(
            $oRecord->getName(),
            new CircularDnaSequence($oRecord->getSequence()),
            $aFeatures,
            $oRecord->getDescription(),
            $oRecord->getExternalId(),
            $oRecord->getMetadata()
        );
    }
}
