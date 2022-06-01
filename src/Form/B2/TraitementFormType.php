<?php

namespace App\Form\B2;

use App\Entity\B2\Observations;
use App\Entity\B2\Traitements;
use App\Repository\B2\ObservationsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ChoiceList;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraitementFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {


        $builder
            ->add('observation', EntityType::class, [
                'class' => Observations::class,
                'label' => 'Statut:',
                'choice_label' => 'name',
                'query_builder' => function (ObservationsRepository $or){
                return $or->createQueryBuilder('o')
                    ->orderBy('o.name', 'ASC');
                }
            ])
            ->add('precisions', TextareaType::class, [
                'label' => "Précision:"
                ]
            )
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Traitements::class,
        ]);
    }
}
