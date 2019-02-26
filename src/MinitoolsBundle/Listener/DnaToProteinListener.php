<?php

namespace MinitoolsBundle\Listener;

use MinitoolsBundle\Entity\DnaToProtein;
use MinitoolsBundle\Service\DnaToProteinManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class DnaToProteinListener implements EventSubscriberInterface
{
    private $dnaToProteinManager;
    private $sRvSequence = "";


    /**
     * DnaToProteinListener constructor.
     * @param DnaToProteinManager $dnaToProteinManager
     */
    public function __construct(DnaToProteinManager $dnaToProteinManager)
    {
        $this->dnaToProteinManager = $dnaToProteinManager;
    }


    /**
     * Listener called when DNA to protein processing
     * @param GenericEvent $event
     * @throws \Exception
     */
    public function onCreateFrames(GenericEvent $event)
    {
        $sResults = '';
        $mycode = null;
        $dnatoprotein = $event->getSubject();
        $sequence = preg_replace("(\W|\d)", "", $dnatoprotein->getSequence());

        if($dnatoprotein->getUsemycode() == 1) {
            $mycode = preg_replace("([^FLIMVSPTAY*HQNKDECWRG\*])", "", $dnatoprotein->getMycode());
            if (strlen($mycode) != 64) {
                throw new \Exception("The custom code is not correct (is not 64 characters long).");
            }
            $dnatoprotein->setGeneticCode("custom");
        }

        if ($dnatoprotein->getProtsize() < 10) {
            throw new \Exception("Minimum size of protein sequence is not correct (minimum size is 10).");
        }

        if ($dnatoprotein->getGeneticCode() == "custom"){
            $aFrames = $this->customTreatment($dnatoprotein, $sequence, $mycode);
        } else {
            $aFrames = $this->definedTreatment($dnatoprotein, $sequence);
        }
        // FIND ORFs
        if ((bool)$dnatoprotein->getSearchOrfs()){
            $aFrames = $this->dnaToProteinManager->findORF($aFrames, $dnatoprotein->getProtsize(), (bool)$dnatoprotein->getOnlyCoding(), (bool)$dnatoprotein->getTrimmed());
        }

        // SHOW TRANSLATIONS ALIGNED (when requested)
        if ((bool)$dnatoprotein->getShowAligned()){
            $sResults = $this->dnaToProteinManager->showTranslationsAligned($sequence,$this->sRvSequence,$aFrames);
        }

        $event->setArgument('frames',$aFrames);
        $event->setArgument('frames_aligned',$sResults);
    }


    /**
     * Call listener
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'on_create_frames' => array('onCreateFrames', -10)
        );
    }


    /**
     * @param   DnaToProtein    $oDnaToProtein
     * @param   string          $sSequence
     * @param   string          $sMycode
     * @return  array
     */
    private function customTreatment(DnaToProtein $oDnaToProtein, $sSequence, $sMycode)
    {
        $aFrames = [];
        // Translate in  5-3 direction
        $aFrames[1] = $this->dnaToProteinManager->translateDNAToProteinCustomcode(substr($sSequence, 0, floor(strlen($sSequence)/3)*3), $sMycode);

        if ($oDnaToProtein->getFrames() > 1){
            $aFrames[2] = $this->dnaToProteinManager->translateDNAToProteinCustomcode(substr($sSequence, 1,floor((strlen($sSequence)-1)/3)*3),$sMycode);
            $aFrames[3] = $this->dnaToProteinManager->translateDNAToProteinCustomcode(substr($sSequence, 2,floor((strlen($sSequence)-2)/3)*3),$sMycode);
        }
        // Translate the complementary sequence
        if ($oDnaToProtein->getFrames() > 3){
            // Get complementary
            $this->sRvSequence = $this->dnaToProteinManager->revCompDNA($sSequence);
            $aFrames[4] = $this->dnaToProteinManager->translateDNAToProteinCustomcode(substr($this->sRvSequence, 0, floor(strlen($this->sRvSequence)/3)*3),$sMycode);
            $aFrames[5] = $this->dnaToProteinManager->translateDNAToProteinCustomcode(substr($this->sRvSequence, 1,floor((strlen($this->sRvSequence)-1)/3)*3),$sMycode);
            $aFrames[6] = $this->dnaToProteinManager->translateDNAToProteinCustomcode(substr($this->sRvSequence, 2,floor((strlen($this->sRvSequence)-2)/3)*3),$sMycode);
        }
        return $aFrames;
    }


    /**
     * Treatment when a specie has been chosen
     * @param   DnaToProtein    $oDnaToProtein
     * @param   string          $sSequence
     * @return  array
     */
    private function definedTreatment(DnaToProtein $oDnaToProtein, $sSequence)
    {
        // Translate in 5-3 direction
        $aFrames[1] = $this->dnaToProteinManager->translateDNAToProtein(substr($sSequence, 0, floor(strlen($sSequence)/3)*3),$oDnaToProtein->getGeneticCode());
        if ($oDnaToProtein->getFrames() > 1){
            $aFrames[2] = $this->dnaToProteinManager->translateDNAToProtein(substr($sSequence, 1,floor((strlen($sSequence)-1)/3)*3),$oDnaToProtein->getGeneticCode());
            $aFrames[3] = $this->dnaToProteinManager->translateDNAToProtein(substr($sSequence, 2,floor((strlen($sSequence)-2)/3)*3),$oDnaToProtein->getGeneticCode());
        }
        // Translate the complementary sequence
        if ($oDnaToProtein->getFrames() > 3){
            // Get complementary
            $this->sRvSequence = $this->dnaToProteinManager->revCompDNA($sSequence);
            //calculate frames 4-6
            $aFrames[4] = $this->dnaToProteinManager->translateDNAToProtein(substr($this->sRvSequence, 0,floor(strlen($this->sRvSequence)/3)*3),$oDnaToProtein->getGeneticCode());
            $aFrames[5] = $this->dnaToProteinManager->translateDNAToProtein(substr($this->sRvSequence, 1,floor((strlen($this->sRvSequence)-1)/3)*3),$oDnaToProtein->getGeneticCode());
            $aFrames[6] = $this->dnaToProteinManager->translateDNAToProtein(substr($this->sRvSequence, 2,floor((strlen($this->sRvSequence)-2)/3)*3),$oDnaToProtein->getGeneticCode());
        }
        return $aFrames;
    }
}