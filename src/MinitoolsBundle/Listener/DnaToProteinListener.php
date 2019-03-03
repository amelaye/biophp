<?php

namespace MinitoolsBundle\Listener;

use MinitoolsBundle\Entity\DnaToProtein;
use MinitoolsBundle\Service\DnaToProteinManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class DnaToProteinListener implements EventSubscriberInterface
{
    private $dnaToProteinManager;
    //private $sRvSequence = "";


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
        try {
            $sResults = '';
            $sResultsComplementary = '';
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

            if($dnatoprotein->getProtsize() < 10) {
                throw new \Exception("Minimum size of protein sequence is not correct (minimum size is 10).");
            }

            if($dnatoprotein->getGeneticCode() == "custom") {
                $aFrames = $this->dnaToProteinManager->customTreatment($dnatoprotein, $sequence, $mycode);
            } else {
                $aFrames = $this->dnaToProteinManager->definedTreatment($dnatoprotein, $sequence);
            }
            // FIND ORFs
            if((bool)$dnatoprotein->getSearchOrfs()) {
                $aFrames = $this->dnaToProteinManager->findORF($aFrames, $dnatoprotein->getProtsize(), (bool)$dnatoprotein->getOnlyCoding(), (bool)$dnatoprotein->getTrimmed());
            }

            // SHOW TRANSLATIONS ALIGNED (when requested)
            if((bool)$dnatoprotein->getShowAligned()) {
                $sResults = $this->dnaToProteinManager->showTranslationsAligned($sequence, $aFrames);
                $sResultsComplementary = $this->dnaToProteinManager->showTranslationsAlignedComplementary($aFrames);
            }

            $event->setArgument('frames',$aFrames);
            $event->setArgument('frames_aligned',$sResults);
            $event->setArgument('frames_aligned_complementary', $sResultsComplementary);
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
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
}