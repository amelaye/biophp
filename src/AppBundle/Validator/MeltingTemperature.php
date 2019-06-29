<?php
/**
 * Recognizes if the Sequence is valid
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 29 june 2019
 * Last modified 29 june 2019
 */
namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class SequenceRecognition
 * @package AppBundle\Validator
 * @Annotation
 */
class MeltingTemperature extends Constraint
{
    public $message = "The oligonucleotide is not valid.";

    public function validatedBy()
    {
        return \get_class($this).'Validator';
    }
}