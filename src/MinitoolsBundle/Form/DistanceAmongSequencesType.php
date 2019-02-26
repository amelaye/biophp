<?php
/**
 * Form DistanceAmongSequencesType
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 26 february 2019
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DistanceAmongSequencesType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface $builder
     * @param   array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $euclidianDistance = [
            "dinucleotides" => 2,
            "trinucleotides" => 3,
            "tetranucleotides" => 4,
            "pentanucleotides" => 5,
            "hexanucleotides" => 6
        ];

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
            ]
        );

        $builder->add(
            'method',
            ChoiceType::class,
            [
                'choices' => [
                    "pearson" => "Pearson distance for z-scores of tetranucleotides (>20000 bp sequences)",
                    "euclidean" => "Euclidean distance for"
                ],
                'expanded' => false,
                'mapped' => false,
                'required' => true,
            ]
        );

        $builder->add(
            'len',
            ChoiceType::class,
            [
                'choices' => $euclidianDistance,
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ]
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Compare sequences",
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
            'data_class' => 'MinitoolsBundle\Entity\DistanceAmongSequences'
        ));
    }
}