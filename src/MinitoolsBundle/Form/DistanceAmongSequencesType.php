<?php
/**
 * Form DistanceAmongSequencesType
 * @author Amélie DUVERNET akka Amelaye
 * Freely inspired by BioPHP's project biophp.org
 * Created 26 february 2019
 * Last modified 23 june 2019
 * RIP Pasha, gone 27 february 2019 =^._.^= ∫
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DistanceAmongSequencesType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface    $builder
     * @param   array                   $options
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
                'constraints' => array(
                    new Length([
                        'max' => 20000,
                        'maxMessage' => 'This service does not handle input requests longer than {{ limit }} bp.'
                    ]),
                )
            ]
        );

        $builder->add(
            'method',
            ChoiceType::class,
            [
                'choices' => [
                    "Pearson distance for z-scores of tetranucleotides (>20000 bp sequences)" => "pearson",
                    "Euclidean distance for" => "euclidean"
                ],
                'multiple' => false,
                'expanded' => true,
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

        /**
         * Formatting Seq before validation
         */
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) {
            $data = $event->getData();
            if (isset($data['seq'])) {
                // remove a couple of things from sequence
                // whatever is before ">", which is the start of the first sequence
                $allsequences = substr($data['seq'], strpos($data['seq'],">"));
                // remove carriage returns ("\r"), but do not remove line feeds ("\n")
                $allsequences = preg_replace("/\r/","", $allsequences);

                $data['seq'] = $allsequences;
                $event->setData($data);
            }
        });
    }
}