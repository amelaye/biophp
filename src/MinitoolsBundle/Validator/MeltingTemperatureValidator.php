<?php
/**
 * Recognizes if the Sequence is valid
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 29 june 2019
 * Last modified 2 november 2019
 */
namespace MinitoolsBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class MeltingTemperatureValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if ($this->countACGT($value) != strlen($value)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    /**
     * Will count number of A, C, G and T bases in the sequence
     * @param   string  $sSequence  is the sequence
     * @return  int
     * @throws \Exception
     */
    public function countACGT($sSequence)
    {
        try {
            $cg = substr_count($sSequence,"A")
                + substr_count($sSequence,"T")
                + substr_count($sSequence,"G")
                + substr_count($sSequence,"C");
            return $cg;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}