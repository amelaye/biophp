<?php
/**
 * SequenceManipulation Form Inputs
 * Freely inspired by BioPHP's project biophp.org
 * Created 21 august 2019
 * Last modified 21 august 2019
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class SkewsType
 * @package MinitoolsBundle\Form
 * @author Amelie DUVERNET aka Amelaye <amelieonline@gmail.com>
 */
class SkewsType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface    $builder
     * @param   array                   $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = 'GGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAG';
        $data .= 'GGACCGGTGGGCGACGGGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGG';
        $data .= 'GCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGC';
        $data .= 'GGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGC';
        $data .= 'CGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGGGGAGTGAGGGGAGCAG';
        $data .= 'TTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGAC';
        $data .= 'GGGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCC';
        $data .= 'GCCGAGGGACCGGTGGGCGACGGGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGC';
        $data .= 'AGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGG';
        $data .= 'CGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGC';
        $data .= 'GGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGGGGAGTGAGGG';
        $data .= 'GAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTG';
        $data .= 'GGCGACGGGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAGGGGAGCAGTTGGGCCAAGATG';
        $data .= 'GCGGCCGCCGAGGGACCGGTGGGCGACGGGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGCGGGAGTGAG';
        $data .= 'GGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACCGGTGGGCGACGGGGGGGAGTGAGGGGAGCAGTTGGGCCAAGATGGCGGCCGCCGAGGGACC';
        $data .= 'GGTGGGCGACGCGG';

        $builder->add(
            'name',
            TextType::class,
            [
                'required' => false,
                'attr' => [
                    'class' => "form-control"
                ],
                'label' => "Name of sequence :"
            ]
        );

        $builder->add(
            'seq',
            TextareaType::class,
            [
                'required' => true,
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 5,
                    'class' => "form-control"
                ],
                'data' => $data,
                'label' => "Copy sequence in the textarea below (up to 5,000,000 bp):",
                'constraints' => array(
                    new Length([
                        'min' => 1,
                        'max' => 10000000,
                        'minMessage' => 'Minumum sequence length: {{ limit }} bp',
                        'maxMessage' => 'Maximum lengt of sequence allowed is {{ limit }} bp.'
                    ]),
                )
            ]
        );

        $builder->add(
            'window',
            ChoiceType::class,
            [
                'choices' => [
                    5000 => 5000,
                    10000 => 10000,
                    50000 => 50000
                ],
                'required'   => false,
                'label' => "Window size : ",
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ]
            ]
        );

        $builder->get('window')->resetViewTransformers();

        $builder->add(
            'window2',
            TextType::class,
            [
                'required' => false,
                'attr' => [
                    'class' => "form-control"
                ],
                'constraints' => array(
                    // check whether $window is in the correct range
                    new LessThan(50000),
                    new GreaterThan(99)
                ),
                'label' => "or"
            ]
        );

        $builder->add(
            'GmC',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show G+C%"
            ]
        );

        $builder->add(
            'GC',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show GC"
            ]
        );

        $builder->add(
            'AT',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show AT-skew"
            ]
        );

        $builder->add(
            'KETO',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show KETO-skew"
            ]
        );

        $builder->add(
            'oskew',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show oligo-skew for "
            ]
        );

        $builder->add(
            'oligo_len',
            ChoiceType::class,
            [
                'choices' => [
                    'dinucleotides' => 2,
                    'trinucleotides' => 3,
                    'tetranucleotides' => 4,
                    'pentanucleotides' => 5,
                    'hexanucleotides' => 6
                ],
                'label' => "Window size : ",
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ]
            ]
        );

        $builder->add(
            'strands',
            ChoiceType::class,
            [
                'choices' => [
                    'one strand' => 1,
                    'both strands' => 2
                ],
                'label' => "Window size : ",
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ]
            ]
        );

        $builder->add(
            'from',
            TextType::class,
            [
                'required' => false,
                'attr' => [
                    'class' => "form-control"
                ],
                'label' => "Show subsequence from "
            ]
        );

        $builder->add(
            'to',
            TextType::class,
            [
                'required' => false,
                'attr' => [
                    'class' => "form-control"
                ],
                'label' => " to "
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Submit",
                'attr' => [
                    'class' => "btn btn-primary"
                ]
            ]
        );

        /**
         * Formatting Seq before validation
         */
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $data = $event->getData();
            if (isset($data['seq'])) {
                $sequence = strtoupper($data["seq"]);
                $sequence = preg_replace("/\W|\d/","",$sequence); // remove non-coding
                $data['seq'] = $sequence;
                $event->setData($data);
            }
            // if name is not specified, name is "sequence"
            if (isset($data['name']) && $data['name'] == "") {
                $data['name'] = "sequence";
                $event->setData($data);
            }
            // custom window size id not submited, use the $window value
            if (isset($data["window2"]) && $data["window2"] != "") {
                $data["window"] = $data["window2"];
                $event->setData($data);
            }
        });
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new Callback([
                    'callback' => [$this, 'validateisReady'],
                ]),
            ]
        ]);
    }

    /**
     * If sequence is to sort to work with, display error
     * @param $object
     * @param ExecutionContextInterface $context
     * @throws \Exception
     */
    public static function validateisReady($object, ExecutionContextInterface $context)
    {
        if (strlen($object["seq"]) < ($object["window"] + 1400)) {
            $context->buildViolation("Sequence is very small for the selected window size.")
                ->addViolation();
        }
    }
}