<?php
/**
 * Form to calculate Oligonucleotide Frequency
 * Freely inspired by BioPHP's project biophp.org
 * Created 31 march 2019
 * Last modified 29 june 2019
 */
namespace MinitoolsBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class OligoNucleotideFrequencyType
 * @package MinitoolsBundle\Form
 * @author Amélie DUVERNET akka Amelaye <amelieonline@gmail.com>
 */
class OligoNucleotideFrequencyType extends AbstractType
{
    /**
     * Form builder
     * @param   FormBuilderInterface $builder
     * @param   array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $sequenceSample = "AACAATGCCATGATGATGATTATTACGACACAACAACACCGCGCTTGACGGCGGCGGATGGATGCCG";
        $sequenceSample .= "CGATCAGACGTTCAACGCCCACGTAACGTAACGCAACGTAACCTAACGACACTGTTAACGGTACGAT";

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
            'len',
            ChoiceType::class,
            [
                'choices' => [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8],
                'label' => "Select length of oligonucleotides :",
                'attr' => [
                    'class' => "form-control"
                ],
            ]
        );

        $builder->add(
            'strands',
            ChoiceType::class,
            [
                'choices' => ["one strand" => 1, "both strands" => 2],
                'label' => "Compute frequencies in :",
                'attr' => [
                    'class' => "form-control"
                ],
            ]
        );


        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => "Find oligonucleotide frequencies",
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
     * When length of query sequence is bellow 4^oligo_len => error (to avoid a lot of 0 frequencies);
     * @param $object
     * @param ExecutionContextInterface $context
     * @throws \Exception
     */
    public static function validateisReady($object, ExecutionContextInterface $context)
    {
        if (strlen($object['sequence']) < pow(4, $object['len'])) {
            $context->buildViolation('Query sequence must be at least 4^(length of oligo) to proceed.')
                ->addViolation();
        }
    }
}