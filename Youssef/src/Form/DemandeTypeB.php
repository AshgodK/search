<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Demande;
use App\Entity\Offre;
use App\Entity\User;

class DemandeTypeB extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('description', TextType::class, [
            'constraints' => [
                new NotBlank(['message' => 'La description doit être renseignée.']), // Custom error message for NotBlank constraint
                new Length(['min' => 10, 'max' => 255, 'minMessage' => 'La description doit avoir au moins {{ limit }} caractères.', 'maxMessage' => 'La description ne peut pas avoir plus de {{ limit }} caractères.']), // Custom error message for Length constraint
                new Type(['type' => 'string', 'message' => 'La description doit être une chaîne de caractères.']), // Custom error message for Type constraint
            ],
        ])
        
            ->add('offre', EntityType::class, [
                'class' => Offre::class,
                'choice_label' => 'id',
                'label' => 'Offre',
                'placeholder' => 'Choisir une option', // Placeholder text for 'offre'
                'required' => true, // Optional: Whether the field is required
                'constraints' => [
                    new NotBlank(['message' => 'Le statut doit être renseigné.']), // Custom error message for NotBlank constraint
                ],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'attr' => ['class' => 'form-control'],
                'label' => 'Utilisateur',
                'placeholder' => 'Choisir une option', // Placeholder text for 'user'
                'required' => true, // Optional: Whether the field is required
                'constraints' => [
                    new NotBlank(['message' => 'Le statut doit être renseigné.']), // Custom error message for NotBlank constraint
                ],
            ]);
            
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Demande::class,
        ]);
    }
}
