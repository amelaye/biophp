<?php
/**
 * Class ReduceAlphabetType
 * Freely inspired by BioPHP's project biophp.org
 * Created 7 april 2019
 * Last modified 7 april 2019
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Form ReduceAlphabetType
 * @package MinitoolsBundle\Form
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class ReduceAlphabetType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface    $builder
     * @param   array                   $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $aTypes = [
            "Do not reduce" => 2,
            "Hydrophilic/Hydrophobic" => 2,
            "Chemical / structural properties" => 5,
            "Chemical / structural properties #2" => 6,
            "3 IMGT amino acid hydropathy alphabet" => '3IMG',
            "5 IMGT amino acid volume alphabet" => '5IMG',
            "11 IMGT amino acid chemical characteristics alphabet" => '11IMG',
            "Murphy et al, 15" => 'Murphy15',
            "Murphy et al, 10" => 'Murphy10',
            "Murphy et al, 8" => 'Murphy8',
            "Murphy et al, 4" => 'Murphy4',
            "Murphy et al, 2" => 'Murphy2',
            "Wang &amp; Wang, 5" => 'Wang5',
            "Wang &amp; Wang, 5 variant" => 'Wang5v',
            "Wang &amp; Wang, 3" => 'Wang3',
            "Wang &amp; Wang, 2" => 'Wang2',
            "Li et al, 10" => 'Li10',
            "Li et al, 5" => 'Li5',
            "Li et al, 4" => 'Li4',
            "Li et al, 3" => 'Li3'
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
                'label' => "Sequence :",
                'data' => 'ARNDCEQGHILKMFPSTWYVX*',
                'required' => true
            ]
        );

        $builder->add(
            'mode',
            ChoiceType::class,
            [
                'choices' => [
                    "Select Reduced alphabet" => "pre",
                    "Use Personalized alphabet" => "custom"
                ],
                'multiple' => false,
                'expanded' => true,
                'required' => true
            ]
        );

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'choices' => $aTypes,
                'attr' => [
                    'class' => "custom-select d-block w-20"
                ],
            ]
        );

        $builder->add(
            'custom_alphabet',
            TextType::class,
            [
                'required' => false,
                'data' => "TCDCTCDTRAACDRDTDRRA",
                'attr' => [
                    'class' => "form-control"
                ],
            ]
        );

        $builder->add(
            'aaperline',
            TextType::class,
            [
                'required' => false,
                'data' => 100,
                'attr' => [
                    'class' => "form-control"
                ],
                'label' => "Aminoacids per line :"
            ]
        );

        $builder->add(
            'show_reduced',
            CheckboxType::class,
            [
                'required' => false,
                'label' => "Show reduced alphabet : "
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
            'data_class' => 'MinitoolsBundle\Entity\ReduceAlphabet'
        ));
    }
}