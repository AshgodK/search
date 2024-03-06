<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Type; 
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class Commande1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('date', DateType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Date cannot be blank']),
                new Type(['type' => '\DateTime', 'message' => 'Date must be a valid date']),
            ],
        ])
        ->add('statut', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Statut cannot be blank']),
            ],
        ])
        ->add('prix', NumberType::class, [
            'constraints' => [
                new NotBlank(['message' => 'Prix cannot be blank']),
                new Type(['type' => 'float', 'message' => 'Prix must be a valid number']),
            ],
        ])
            ->add('telephone')
            ->add('service')
            ->add('user', EntityType::class, [
                'class' => 'App\Entity\User',
                'choice_label' => 'username', 
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
