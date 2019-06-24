<?php
/**
 * Recognizes if the Sequence is valid
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 24 june 2019
 * Last modified 24 june 2019
 */
namespace AppBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SequenceRecognitionValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $sSequence = strtoupper($value);
        $sSequence = preg_replace("/\\W|\\d/","", $sSequence);
        $sSequence = preg_replace("/X/","N", $sSequence);

        if($sSequence == "") {
            $this->context->buildViolation($constraint->messageEmpty)
                ->addViolation();
        }

        $len_seq = strlen($sSequence);
        $number_ATGC = $this->countACGT($sSequence);
        $number_YRWSKMDVHB = $this->countYRWSKMDVHB($sSequence);
        $number = $number_ATGC + $number_YRWSKMDVHB + substr_count($sSequence,"N");

        if ($number != $len_seq) {
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

    /**
     * Will count number of degenerate nucleotides (Y, R, W, S, K, MD, V, H and B) in the sequence
     * @param   string $c
     * @return  int
     * @throws \Exception
     */
    public function countYRWSKMDVHB($c){
        try {
            $cg = substr_count($c,"Y")
                + substr_count($c,"R")
                + substr_count($c,"W")
                + substr_count($c,"S")
                + substr_count($c,"K")
                + substr_count($c,"M")
                + substr_count($c,"D")
                + substr_count($c,"V")
                + substr_count($c,"H")
                + substr_count($c,"B");
            return $cg;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    /**
     * @param $c
     * @return int
     * @throws \Exception
     */
    public function countCG($c)
    {
        try {
            $cg = substr_count($c,"G")
                + substr_count($c,"C");
            return $cg;
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }
}