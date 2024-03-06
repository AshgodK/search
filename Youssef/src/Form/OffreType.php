<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Offre;
use App\Entity\User;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le nom doit être renseigné.']), // Custom error message for NotBlank constraint
                    new Length(['min' => 4, 'max' => 255, 'minMessage' => 'Le nom doit avoir au moins {{ limit }} caractères.', 'maxMessage' => 'Le nom ne peut pas avoir plus de {{ limit }} caractères.']), // Custom error message for Length constraint
                    new Type(['type' => 'string', 'message' => 'Le nom doit être une chaîne de caractères.']), // Custom error message for Type constraint
                ],
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La description doit être renseignée.']), // Custom error message for NotBlank constraint
                    new Length(['min' => 10, 'max' => 255, 'minMessage' => 'La description doit avoir au moins {{ limit }} caractères.', 'maxMessage' => 'La description ne peut pas avoir plus de {{ limit }} caractères.']), // Custom error message for Length constraint
                    new Type(['type' => 'string', 'message' => 'La description doit être une chaîne de caractères.']), // Custom error message for Type constraint
                ],
            ])
            ->add('echances', DateType::class, [
                'html5' => true,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => new \DateTime('tomorrow'),
                        'message' => 'Please select a date from tomorrow or later.',
                    ]),
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Initialisation' => 'Initialisation',
                    'Encours' => 'Encours',
                    'Terminer' => 'Terminer',
                    // Add more options as needed
                ],
                'placeholder' => 'Choose an option', // Optional: Placeholder text
                'required' => true, // Optional: Whether the field is required
                'constraints' => [
                    new NotBlank(['message' => 'Le statut doit être renseigné.']), // Custom error message for NotBlank constraint
                ],
            ])
            ->add('prix', NumberType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Le prix doit être renseigné.']), // Custom error message for NotBlank constraint
                    new Type(['type' => 'numeric', 'message' => 'Le prix doit être numérique.']), // Custom error message for Type constraint
                ],
            ])
            ->remove('idUser');

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}

