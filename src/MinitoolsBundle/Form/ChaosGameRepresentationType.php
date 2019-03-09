<?php
/**
 * Form ChaosGameRepresentationType
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 9 march 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChaosGameRepresentationType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface $builder
     * @param   array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sampleADN = "GTGCCGAGCTGAGTTCCTTATAAGAATTAATCTTAATTTTGTATTTTTTCCTGTAAGACAATAGGCCATG";
        $sampleADN .= "TTAATTAAACTGAAGAAGGATATATTTGGCTGGGTGTTTTCAAATGTCAGCTTAAAATTGGTAATTGAAT";
        $sampleADN .= "GGAAGCAAAATTATAAGAAGAGGAAATTAAAGTCTTCCATTGCATGTATTGTAAACAGAAGGAGATGGGT";
        $sampleADN .= "GATTCCTTCAATTCAAAAGCTCTCTTTGGAATGAACAATGTGGGCGTTTGTAAATTCTGGAAATGTCTTT";
        $sampleADN .= "CTATTCATAATAAACTAGATACTGTTGATCTTTTAAAAAAAAAAAA";

        $optionsSize = array(
            "Auto" => "auto",
            "1024 X 1024" => "1024",
            "512 x 512" => "512",
            "256 x 256" => "256",
        );

        $builder->add(
            'seq_name',
            TextType::class,
            [
                'label' => "Sequence name",
                'attr' => [
                    'class' => "form-control"
                ]
            ]
        );

        $builder->add(
            'size',
            ChoiceType::class,
            [
                'choices' => $optionsSize,
                'label' => "Sequence name",
                'attr' => [
                    'class' => "form-control"
                ]
            ]
        );

        $builder->add(
            's',
            ChoiceType::class,
            [
                'choices' => [
                    "Both strands" => 2,
                    "Only upper strand" => 1
                ],
                'label' => "Compute data for",
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ]
            ]
        );

        $builder->add(
            'len',
            ChoiceType::class,
            [
                'choices' => [
                    2 => '2',
                    3 => '3',
                    4 => '4',
                    5 => '5',
                    6 => '6',
                    7 => '7'
                ],
                'label' => "Search oligos of length",
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ]
            ]
        );

        $builder->add(
            'seq',
            TextareaType::class,
            [
                'attr' => [
                    'cols'  => 75,
                    'rows'  => 10,
                    'class' => "form-control"
                ],
                'label' => "Sequence : ",
                'data'  => $sampleADN
            ]
        );

        $builder->add(
            'map',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show as image map (not recomemded for long oligonuclotides)"
            ]
        );

        $builder->add(
            'freq',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show oligonuclotide frequencies"
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Create FCGR image",
                'attr' => [
                    'class' => "btn btn-primary"
                ]
            ]
        );
    }


    /**
     * Entity for builder
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MinitoolsBundle\Entity\ChaosGameRepresentation'
        ));
    }
}