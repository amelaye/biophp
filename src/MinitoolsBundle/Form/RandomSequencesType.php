<?php
/**
 * Form RandomSequencesType
 * Freely inspired by BioPHP's project biophp.org
 * Created 6 april 2019
 * Last modified 6 april 2019
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Class RandomSequencesType
 * @package MinitoolsBundle\Form
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class RandomSequencesType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface    $builder
     * @param   array                   $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'procedure',
            ChoiceType::class,
            [
                'choices' => [
                    "fromseq",
                    "fromACGT",
                    "fromAA"
                ],
                'multiple' => false,
                'expanded' => true,
                'required' => true
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
                'label' => "Row sequence to be randomized (bp) :",
                'required' => false
            ]
        );

        $builder->add(
            'length1',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control"
                ],
                'label' => "Generate a random sequence of length (with composition above) :",
                'required' => false
            ]
        );

        $builder->add(
            'length2',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control"
                ],
                'label' => "Generate random DNA sequence of length (and composition below) :",
                'required' => false
            ]
        );

        $builder->add(
            'length3',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control"
                ],
                'label' => "Generate random DNA sequence of length (and composition below) :",
                'required' => false
            ]
        );

        $builder->add(
            'dnaA',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "A :",
                'data' => 29.5,
                'required' => false
            ]
        );

        $builder->add(
            'dnaT',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "T :",
                'data' => 29.5,
                'required' => false
            ]
        );

        $builder->add(
            'dnaG',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "G :",
                'data' => 20.5,
                'required' => false
            ]
        );

        $builder->add(
            'dnaC',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "C :",
                'data' => 20.5,
                'required' => false
            ]
        );

        $builder->add(
            'a',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "A :",
                'data' => 1.174,
                'required' => false
            ]
        );

        $builder->add(
            'c',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "C :",
                'data' => 2.395,
                'required' => false
            ]
        );


        $builder->add(
            'd',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "D :",
                'data' => 4.872,
                'required' => false
            ]
        );

        $builder->add(
            'e',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "E :",
                'data' => 6.662,
                'required' => false
            ]
        );

        $builder->add(
            'f',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "F :",
                'data' => 3.624,
                'required' => false
            ]
        );

        $builder->add(
            'g',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "G :",
                'data' => 7.532,
                'required' => false
            ]
        );

        $builder->add(
            'h',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "H :",
                'data' => 7.532,
                'required' => false
            ]
        );

        $builder->add(
            'i',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "I :",
                'data' => 7.532,
                'required' => false
            ]
        );

        $builder->add(
            'k',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "K :",
                'data' => 5.635,
                'required' => false
            ]
        );

        $builder->add(
            'l',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "L :",
                'data' => 9.412,
                'required' => false
            ]
        );

        $builder->add(
            'm',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "M :",
                'data' => 2.196,
                'required' => false
            ]
        );

        $builder->add(
            'n',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "N :",
                'data' => 3.789,
                'required' => false
            ]
        );

        $builder->add(
            'p',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "P :",
                'data' => 6.294,
                'required' => false
            ]
        );

        $builder->add(
            'q',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "Q :",
                'data' => 4.509,
                'required' => false
            ]
        );

        $builder->add(
            'r',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "R :",
                'data' => 5.607,
                'required' => false
            ]
        );

        $builder->add(
            's',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "S :",
                'data' => 7.527,
                'required' => false
            ]
        );

        $builder->add(
            't',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "T :",
                'data' => 5.685,
                'required' => false
            ]
        );

        $builder->add(
            'v',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "V :",
                'data' => 6.026,
                'required' => false
            ]
        );

        $builder->add(
            'w',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "W :",
                'data' => 1.480,
                'required' => false
            ]
        );

        $builder->add(
            'y',
            TextType::class,
            [
                'attr' => [
                    'class' => "form-control",
                ],
                'label' => "Y :",
                'data' => 2.840,
                'required' => false
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

    /**
     * Entity for builder
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MinitoolsBundle\Entity\RandomSequences'
        ));
    }
}