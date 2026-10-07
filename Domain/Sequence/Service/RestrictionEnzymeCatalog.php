<?php
/**
 * Typed restriction enzyme catalog, merging Type II, Type IIb and Type IIs endonuclease sources
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 September 2026
 * Last modified 7 October 2026
 */
declare(strict_types=1);

namespace Amelaye\BioPHP\Domain\Sequence\Service;

use Amelaye\BioPHP\Api\Interfaces\TypeIIbEndonucleaseApiAdapter;
use Amelaye\BioPHP\Api\Interfaces\TypeIIEndonucleaseApiAdapter;
use Amelaye\BioPHP\Api\Interfaces\TypeIIsEndonucleaseApiAdapter;
use Amelaye\BioPHP\Domain\Sequence\Exception\UnknownRestrictionEnzymeException;
use Amelaye\BioPHP\Domain\Sequence\Interfaces\RestrictionEnzymeCatalogInterface;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\DnaSequence;
use Amelaye\BioPHP\Domain\Sequence\ValueObject\RestrictionEnzymeDefinition;

/**
 * Class RestrictionEnzymeCatalog - composes the three existing endonuclease adapters into a single,
 * typed lookup of RestrictionEnzymeDefinition, without duplicating bioapi's data and without touching
 * RestrictionEnzymeManager's private storage. Built once at construction, since the underlying sources
 * are themselves immutable API snapshots.
 * @package Amelaye\BioPHP\Domain\Sequence\Service
 * @author Amélie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class RestrictionEnzymeCatalog implements RestrictionEnzymeCatalogInterface
{
    /**
     * @var     RestrictionEnzymeDefinition[]   Keyed by uppercase canonical name
     */
    private array $aDefinitionsByName = [];

    /**
     * @var     string[]        Uppercase name or alias => uppercase canonical name
     */
    private array $aCanonicalNameByAlias = [];

    /**
     * RestrictionEnzymeCatalog constructor.
     * @param   TypeIIEndonucleaseApiAdapter    $typeIIApi
     * @param   TypeIIbEndonucleaseApiAdapter   $typeIIbApi
     * @param   TypeIIsEndonucleaseApiAdapter   $typeIIsApi
     */
    public function __construct(
        TypeIIEndonucleaseApiAdapter $typeIIApi,
        TypeIIbEndonucleaseApiAdapter $typeIIbApi,
        TypeIIsEndonucleaseApiAdapter $typeIIsApi
    ) {
        $this->registerDtoCollection($typeIIApi->getTypeIIEndonucleases(), RestrictionEnzymeDefinition::TYPE_II);
        $this->registerDtoCollection($typeIIbApi->getTypeIIbEndonucleases(), RestrictionEnzymeDefinition::TYPE_IIB);
        $this->registerDtoCollection($typeIIsApi->getTypeIIsEndonucleases(), RestrictionEnzymeDefinition::TYPE_IIS);
    }

    /**
     * @inheritDoc
     */
    public function findByName(string $sName): ?RestrictionEnzymeDefinition
    {
        $sKey = strtoupper(trim($sName));

        if (!array_key_exists($sKey, $this->aCanonicalNameByAlias)) {
            return null;
        }

        return $this->aDefinitionsByName[$this->aCanonicalNameByAlias[$sKey]];
    }

    /**
     * @inheritDoc
     */
    public function getByName(string $sName): RestrictionEnzymeDefinition
    {
        $oDefinition = $this->findByName($sName);

        if ($oDefinition === null) {
            throw UnknownRestrictionEnzymeException::forName($sName);
        }

        return $oDefinition;
    }

    /**
     * @inheritDoc
     */
    public function findByFamily(string $sFamily): array
    {
        $aResult = array_values(array_filter(
            $this->aDefinitionsByName,
            function (RestrictionEnzymeDefinition $oDefinition) use ($sFamily) {
                return $oDefinition->getFamily() === $sFamily;
            }
        ));

        return $this->sortedByName($aResult);
    }

    /**
     * @inheritDoc
     */
    public function findByRecognitionSequence(string $sSequence): array
    {
        $sNormalized = strtoupper(trim($sSequence));

        $aResult = array_values(array_filter(
            $this->aDefinitionsByName,
            function (RestrictionEnzymeDefinition $oDefinition) use ($sNormalized) {
                return in_array($sNormalized, $this->sitesOf($oDefinition), true);
            }
        ));

        return $this->sortedByName($aResult);
    }

    /**
     * Lists the sites an enzyme binds, upper-cased : each alternative of a "SITE1 or SITE2"
     * recognition sequence (AciI CCGC or GCGG), and the reverse complement of each, the same site
     * read on the other strand (AccBSI CCGCTC, GAGCGG).
     * @param   RestrictionEnzymeDefinition     $oDefinition
     * @return  string[]
     */
    private function sitesOf(RestrictionEnzymeDefinition $oDefinition): array
    {
        $aSites = [];
        foreach (preg_split('/\s+or\s+/i', trim($oDefinition->getCleanRecognitionSequence())) as $sSite) {
            $sSite = strtoupper(trim($sSite));
            $aSites[] = $sSite;
            if (preg_match('/^[ACGTRYSWKMBDHVN]+$/', $sSite) === 1) {
                $aSites[] = (new DnaSequence($sSite))->reverseComplement()->getValue();
            }
        }
        return $aSites;
    }

    /**
     * Maps every DTO of a collection to a RestrictionEnzymeDefinition and registers it, keeping the
     * first-registered definition whenever the same canonical name is found in more than one source.
     * An entry whose name ends with "@" (FokI@) is not an enzyme : it is the reverse orientation of
     * the Type IIS site of the enzyme it follows, kept for pattern searches. A digest already finds
     * that orientation by searching both strands, so it is not registered.
     * @param   array       $aDtos      TypeIIEndonucleaseDTO[]|TypeIIbEndonucleaseDTO[]|TypeIIsEndonucleaseDTO[]
     * @param   string      $sFamily    One of RestrictionEnzymeDefinition::VALID_FAMILIES
     */
    private function registerDtoCollection(array $aDtos, string $sFamily): void
    {
        foreach ($aDtos as $oDto) {
            if (substr($oDto->getId(), -1) === "@") {
                continue;
            }

            $oDefinition = $this->mapDtoToDefinition($oDto, $sFamily);
            $sKey = strtoupper($oDefinition->getName());

            if (array_key_exists($sKey, $this->aDefinitionsByName)) {
                continue;
            }

            $this->aDefinitionsByName[$sKey] = $oDefinition;
            $this->aCanonicalNameByAlias[$sKey] = $sKey;

            foreach ($oDefinition->getAliases() as $sAlias) {
                $sAliasKey = strtoupper($sAlias);

                if (!array_key_exists($sAliasKey, $this->aCanonicalNameByAlias)) {
                    $this->aCanonicalNameByAlias[$sAliasKey] = $sKey;
                }
            }
        }
    }

    /**
     * Builds a RestrictionEnzymeDefinition from a Type II, IIb or IIs DTO. The three DTO classes share
     * the same eight getters by construction; only their class name and family differ.
     * @param   object      $oDto       TypeIIEndonucleaseDTO|TypeIIbEndonucleaseDTO|TypeIIsEndonucleaseDTO
     * @param   string      $sFamily    One of RestrictionEnzymeDefinition::VALID_FAMILIES
     * @return  RestrictionEnzymeDefinition
     */
    private function mapDtoToDefinition(object $oDto, string $sFamily) : RestrictionEnzymeDefinition {
        $sName = $oDto->getId();
        $aSamePattern = $oDto->getSamePattern();
        $aAliases = [];

        if (!empty($aSamePattern[0])) {
            $aAliases = array_values(array_filter(
                array_map("trim", explode(",", $aSamePattern[0])),
                function (string $sAlias) use ($sName) {
                    return $sAlias !== "" && strcasecmp($sAlias, $sName) !== 0;
                }
            ));
        }

        return new RestrictionEnzymeDefinition(
            $sName,
            $aAliases,
            $sFamily,
            $oDto->getRecognitionPattern(),
            $oDto->getComputingPattern(),
            $oDto->getLengthRecognitionPattern(),
            $oDto->getCleavagePosUpper(),
            $oDto->getCleavagePosLower(),
            $oDto->getNbNonNBases()
        );
    }

    /**
     * @param   RestrictionEnzymeDefinition[]   $aDefinitions
     * @return  RestrictionEnzymeDefinition[]   Sorted by canonical name, ascending
     */
    private function sortedByName(array $aDefinitions): array
    {
        usort($aDefinitions, function (RestrictionEnzymeDefinition $oLeft, RestrictionEnzymeDefinition $oRight) {
            return strcmp($oLeft->getName(), $oRight->getName());
        });

        return $aDefinitions;
    }
}
