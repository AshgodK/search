<?php

namespace App\Form;

use App\Entity\Offre;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert;

class OffreTypeB extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('description', TextType::class)
            ->add('echances', DateType::class, [
                'html5' => true,
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
            ])
            ->add('prix', TextType::class)
            ->add('idUser', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id', // Adjust this to the property you want to display
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Offre::class,
        ]);
    }
}
