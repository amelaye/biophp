<?php
/**
 * Form ProteinPropertiesType
 * Freely inspired by BioPHP's project biophp.org
 * Created 31 march 2019
 * Last modified 29 june 2019
 */

namespace MinitoolsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class MicrosatelliteRepeatsFinderType
 * @package MinitoolsBundle\Form
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class MicrosatelliteRepeatsFinderType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface  $builder
     * @param   array                 $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sequenceSample = "AACAATGCCATGATGATGATTATTACGACACAACAACACCGCGCTTGACGGCGGCGGATGGATGCCG";
        $sequenceSample .= "CGATCAGACGTTCAACGCCCACGTAACGTAACGCAACGTAACCTAACGACACTGTTAACGGTACGAT";


        $dataMin = [2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6];
        $dataMax = [3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10];
        $minRepeats = [2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6];
        $lengthOfMR = [5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10, 11 => 11, 12 => 12, 13 => 13, 14 => 14,
            15 => 15, 16 => 16, 17 => 17, 18 => 18, 19 => 19, 20 => 20];
        $mismatch = [0 => 0, 10 => 10, 20 => 20, 30 => 30];

        $builder->add(
            'sequence',
            TextareaType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 10,
                    'class' => "form-control"
                ],
                'label' => "Sequence : ",
                'data' => $sequenceSample
            ]
        );

        $builder->add(
            'min',
            ChoiceType::class,
            [
                'choices' => $dataMin,
                'label' => "Minimum length of repeated sequence :",
                'attr' => [
                    'class' => "form-control"
                ],
            ]
        );

        $builder->add(
            'max',
            ChoiceType::class,
            [
                'choices' => $dataMax,
                'label' => "Maximum length of repeated sequence :",
                'attr' => [
                    'class' => "form-control"
                ],
                'choice_attr' => function($max) {
                    $attr = [];
                    if ($max === 6) {
                        $attr['selected'] = 'selected';
                    }
                    return $attr;
                }
            ]
        );

        $builder->add(
            'min_repeats',
            ChoiceType::class,
            [
                'choices' => $minRepeats,
                'label' => "Minimum number of repeats :",
                'attr' => [
                    'class' => "form-control"
                ],
                'choice_attr' => function($repeats) {
                    $attr = [];
                    if ($repeats === 3) {
                        $attr['selected'] = 'selected';
                    }
                    return $attr;
                }
            ]
        );

        $builder->add(
            'length_of_MR',
            ChoiceType::class,
            [
                'choices' => $lengthOfMR,
                'label' => "Minimum length of tandem repeat : ",
                'attr' => [
                    'class' => "form-control"
                ],
                'choice_attr' => function($length) {
                    $attr = [];
                    if ($length === 6) {
                        $attr['selected'] = 'selected';
                    }
                    return $attr;
                }
            ]
        );

        $builder->add(
            'mismatch',
            ChoiceType::class,
            [
                'choices' => $mismatch,
                'label' => "Allowed percentage of mismatches :",
                'attr' => [
                    'class' => "form-control"
                ]
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Find Microsatellite repeats",
                'attr' => [
                    'class' => "btn btn-primary"
                ]
            ]
        );

        /**
         * Formatting Seq before validation
         * Remove non word and digits from sequence
         */
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $data = $event->getData();

            if (isset($data['sequence'])) {
                $sSequence = strtoupper($data['sequence']);
                $sSequence = preg_replace("/\W|\d/", "", $sSequence);

                $data['sequence'] = $sSequence;
                $event->setData($data);
            }
        });
    }
}