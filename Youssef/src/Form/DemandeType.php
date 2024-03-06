<?php

namespace App\Form;

use App\Entity\Demande;
use App\Entity\Offre;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DemandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('description', TextareaType::class, [
            'constraints' => [
                new NotBlank(['message' => 'La description doit être renseignée.']), // Custom error message for NotBlank constraint
                new Length(['min' => 10, 'minMessage' => 'La description doit avoir au moins {{ limit }} caractères.']), // Custom error message for Length constraint
                new Type(['type' => 'string', 'message' => 'La description doit être une chaîne de caractères.']), // Custom error message for Type constraint
            ],
            'attr' => [
                'class' => 'form-control', // Add any additional classes here // Adjust the number of visible rows
                'style' => 'width: 350px;', // Custom CSS style to increase height
                // Other attributes as needed
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
