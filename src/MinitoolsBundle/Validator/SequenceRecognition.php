<?php
/**
 * Recognizes if the Sequence is valid
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 june 2019
 * Last modified 2 november 2019
 */
namespace MinitoolsBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class SequenceRecognition
 * @package AppBundle\Validator
 * @Annotation
 */
class SequenceRecognition extends Constraint
{
    public $message = "Sequence is not valid. At least one letter in the sequence is unknown (not a NC-UIBMB valid code)";
    public $messageEmpty = "No sequence available";

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }
}