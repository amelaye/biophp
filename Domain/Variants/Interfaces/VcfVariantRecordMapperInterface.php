<?php
/**
 * VCF variant persistence mapping Interface
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 October 2026
 * Last modified 6 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Variants\Interfaces;

use Amelaye\BioPHP\Domain\Variants\Entity\VcfVariantRecord;
use Amelaye\BioPHP\Domain\Variants\ValueObject\VcfVariant;

/**
 * Interface VcfVariantRecordMapperInterface - converts between the immutable VcfVariant value object
 * and its Doctrine storage shape.
 * @package Amelaye\BioPHP\Domain\Variants\Interfaces
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
interface VcfVariantRecordMapperInterface
{
    /**
     * @param   VcfVariant          $oVariant
     * @return  VcfVariantRecord    A new, unmanaged record
     */
    public function toRecord(VcfVariant $oVariant): VcfVariantRecord;

    /**
     * @param   VcfVariantRecord    $oRecord
     * @return  VcfVariant
     * @throws  \Amelaye\BioPHP\Domain\Variants\Exception\InvalidVcfRecordException     When the
     * stored data no longer satisfies the value object's invariants
     */
    public function toVariant(VcfVariantRecord $oRecord): VcfVariant;
}
