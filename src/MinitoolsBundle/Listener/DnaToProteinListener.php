<?php

namespace MinitoolsBundle\Listener;

use MinitoolsBundle\Service\DnaToProteinManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class DnaToProteinListener implements EventSubscriberInterface
{
    private $dnaToProteinManager;


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

            $dnatoprotein = $event->getSubject(); // Object injected

            $sequence = preg_replace("(\W|\d)", "", $dnatoprotein->getSequence());

            if($dnatoprotein->getUsemycode() == 1) {
                $mycode = preg_replace("([^FLIMVSPTAY*HQNKDECWRG\*])", "", $dnatoprotein->getMycode());
                $dnatoprotein->setMycode($mycode);
                $dnatoprotein->setGeneticCode("custom");
            }

            // Custom code
            if($dnatoprotein->getGeneticCode() == "custom") {
                $aFrames = $this->dnaToProteinManager->customTreatment($dnatoprotein, $sequence, $mycode);
            } else {
                $aFrames = $this->dnaToProteinManager->definedTreatment($dnatoprotein, $sequence);
            }

            // FIND ORFs (when requested)
            if((bool)$dnatoprotein->getSearchOrfs()) {
                $aFrames = $this->dnaToProteinManager->findORF($aFrames, $dnatoprotein->getProtsize(), (bool)$dnatoprotein->getOnlyCoding(), (bool)$dnatoprotein->getTrimmed());
            }

            // Show translations aligned (when requested)
            if((bool)$dnatoprotein->getShowAligned()) {
                $sResults = $this->dnaToProteinManager->showTranslationsAligned($sequence, $aFrames);
                $sResultsComplementary = $this->dnaToProteinManager->showTranslationsAlignedComplementary($aFrames);
            }

            // Output the amino acids with double gaps (--)
            if ($dnatoprotein->getDgaps() == 1) {
                foreach($aFrames as &$line) {
                    $line = chunk_split($line,1,'--');
                }
            }

            // Formats frames
            foreach($aFrames as &$line) {
                $line = chunk_split($line,100,'<br>');
            }

            $event->setArgument('frames', $aFrames);
            $event->setArgument('frames_aligned',$sResults);
            $event->setArgument('frames_aligned_compl', $sResultsComplementary);
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