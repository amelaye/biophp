<?php
/**
 * Factory for SequenceManager service
 * Inspired by BioPHP's project biophp.org
 * Created 13 december 2019
 * Last modified 13 december 2019
 */
namespace Factory;

use AppBundle\Api\ApiAdapterInterface;
use AppBundle\Entity\Sequencing\Sequence;
use AppBundle\Interfaces\SequenceInterface;
use AppBundle\Service\SequenceManager;

class SequenceFactory implements SequenceInterface
{
    private $sequence;

    private $bioapi;

    private $sequenceManager;

    public function __construct(SequenceManager $sequenceManager, ApiAdapterInterface $bioapi)
    {
        $this->sequenceManager = $sequenceManager;
        $this->bioapi = $bioapi;
    }

    /**
     * Injection Sequence
     * @param Sequence $oSequence
     */
    public function setSequence($oSequence)
    {
        $this->sequence = $oSequence;
    }

    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * Returns a string representing the genetic complement of a sequence.
     * @param  string    $sMoltypeUnfrmtd      The type of molecule we are dealing with. If omitted,
     * we work with "DNA" by default.
     * @param null $sSequence
     * @throws \Exception
     */
    public function complement($sMoltypeUnfrmtd, $sSequence = null)
    {
        $aComplements = [];

        if($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }

        if (!isset($sMoltypeUnfrmtd)) {
            $sMoltypeUnfrmtd = (null !== $this->sequence->getMoltype()) ? $this->sequence->getMoltype() : "DNA";
        }

        if (strtoupper($sMoltypeUnfrmtd) == "DNA") {
            $aComplements = $this->bioapi->getDNAComplement();
        } elseif (strtoupper($sMoltypeUnfrmtd) == "RNA") {
            $aComplements = $this->bioapi->getRNAComplement();
        }

        $this->sequenceManager->complement($sSequence, $aComplements);
    }

    /**
     * @param int $iIndex
     * @param null $sSequence
     * @return string|void
     * @throws \Exception
     */
    public function halfSequence($iIndex, $sSequence = null)
    {
        try {
            if($sSequence == null) {
                $sSequence = $this->sequence->getSequence();
            }

            $this->sequenceManager->halfSequence($sSequence, $iIndex);
        } catch (\Exception $ex) {
            throw new \Exception($ex);
        }
    }

    /**
     * @param string $string
     * @return string|void
     */
    public function getBridge($string)
    {
        $this->sequenceManager->getBridge($string);
    }

    /**
     * @param null $sSequence
     * @return string|void
     * @throws \Exception
     */
    public function expandNa($sSequence = null)
    {
        if($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }

        $this->sequenceManager->expandNa($sSequence);
    }

    /**
     * @param null $sSequence
     * @param null $sMolType
     * @param null $NA_len
     * @param string $sLimit
     * @return bool|float|void
     * @throws \Exception
     */
    public function molwt($sSequence = null, $sMolType = null, $NA_len = null, $sLimit = "upperlimit")
    {
        if($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }

        if($sMolType == null) {
            $sMolType  = $this->sequence->getMoltype();
        }

        if($NA_len == null) {
            $NA_len = $this->sequence->getSeqlength();
        }

        if($sSequence == null || $sMolType == null || $NA_len == null) {
            throw new \InvalidArgumentException("Cannot load molwt() method, needs all the arguments.");
        }
        if(!is_string($sSequence)) {
            throw new \InvalidArgumentException("The sequence needs to be string format !");
        }

        $this->sequenceManager->molwt($sSequence, $sMolType, $NA_len, $sLimit);
    }

    /**
     * @param null $aFeatures
     * @param null $iSeqLength
     * @return int|void
     */
    public function countCodons($aFeatures = null, $iSeqLength = null)
    {
        if($aFeatures == null) {
            $aFeatures = $this->sequence->getFeatures();
        }

        if($iSeqLength == null) {
            $iSeqLength = $this->sequence->getSeqlength();
        }

        $this->sequenceManager->countCodons($aFeatures, $iSeqLength);
    }

    /**
     * @param int $iStart
     * @param int $iCount
     * @param null $sSequence
     * @return bool|string|void
     * @throws \Exception
     */
    public function subSeq($iStart, $iCount, $sSequence = null)
    {
        if($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }

        $this->sequenceManager->subSeq($iStart, $iCount, $sSequence);
    }

    /**
     * @param string $sPattern
     * @param null $sSequence
     * @param string $sOptions
     * @return array|void
     * @throws \Exception
     */
    public function patPos($sPattern, $sSequence = null, $sOptions = "I")
    {
        if($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }

        $this->sequenceManager->patPos($sPattern, $sSequence, $sOptions);
    }

    /**
     * @param string $sPattern
     * @param null $sSequence
     * @param string $sOptions
     * @param int $iCutPos
     * @return array|void
     * @throws \Exception
     */
    public function patPoso($sPattern, $sSequence = null, $sOptions = "I", $iCutPos = 1)
    {
        if($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }

        $this->sequenceManager->patPoso($sPattern, $sSequence, $sOptions, $iCutPos);
    }

    /**
     * @param string $sPattern
     * @param string $sOptions
     * @return array|void
     * @throws \Exception
     */
    public function patFreq($sPattern, $sOptions = "I")
    {
        $this->sequenceManager->patFreq($sPattern, $sOptions);
    }

    public function findPattern($sPattern, $sSequence = null, $sOptions = "I")
    {
        if($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }

        $this->sequenceManager->findPattern($sPattern, $sSequence, $sOptions = "I");
    }

    public function symFreq($sSymbol, $sSequence = null)
    {
        if($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }

        $this->sequenceManager->symFreq($sSymbol, $sSequence);
    }

    public function getCodon($iIndex, $sSequence = null, $iReadFrame = 0)
    {
        if($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }

        $this->sequenceManager->getCodon($iIndex, $sSequence, $iReadFrame = 0);
    }

    public function translate($iReadFrame = 0, $iFormat = 1)
    {
        $this->sequenceManager->translate($iReadFrame, $iFormat);
    }

    public function charge($sAminoSeq)
    {
        $this->sequenceManager->charge($sAminoSeq);
    }

    public function chemicalGroup($sAminoSeq)
    {
        $this->sequenceManager->chemicalGroup($sAminoSeq);
    }

    public function translateCodon($sCodon, $iFormat = 3)
    {
        $this->sequenceManager->translateCodon($sCodon, $iFormat = 3);
    }

    public function isMirror($sSequence = null)
    {
        if ($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }
        $this->sequenceManager->isMirror($sSequence);
    }

    public function findMirror($sSequence = null, $iPallen1 = null, $iPallen2 = null, $sOptions = "E")
    {
        if ($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
            $iSeqLength = strlen($sSequence);
            if ($iSeqLength == 0) {
                return false;
            }
        }

        $iSeqLength = strlen($sSequence);

        if (!isset($iPallen1) || (isset($iPallen1) && (($iPallen1 < 2)
                    || ($iPallen1 > $iSeqLength) || (!is_int($iPallen1))))) {
            return false;
        }

        if (!is_int($iPallen2)) {
            return false;
        } else {
            if (($iPallen2 < $iPallen1)) {
                return false;
            }
        }

        $this->sequenceManager->findMirror($sSequence, $iPallen1, $iPallen2, $sOptions);
    }

    public function isPalindrome($sSequence = null)
    {
        if ($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }
        $this->sequenceManager->isPalindrome($sSequence);
    }

    public function findPalindrome($sSequence, $iSeqLen = null, $iPalLen = null)
    {
        if($sSequence == null) {
            $sSequence = $this->sequence->getSequence();
        }
        $this->sequenceManager->findPalindrome($sSequence, $iSeqLen, $iPalLen);
    }
}