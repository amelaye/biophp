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
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 5,
                    'class' => "form-control"
                ],
                'label' => "Copy sequence in the textarea below (up to 5,000,000 bp):",
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
                'label' => "Window size : ",
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ]
            ]
        );

        $builder->add(
            'window2',
            TextType::class,
            [
                'required' => false,
                'attr' => [
                    'class' => "form-control"
                ],
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
    }
}