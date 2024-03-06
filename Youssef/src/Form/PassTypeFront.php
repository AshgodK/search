<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\NotBlank;

class PassTypeFront extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('OldPass', PasswordType::class, [
            'label' => 'Old Password',
            'constraints' => [
                new NotBlank(),
                // You can add other constraints for password strength here
            ],
        ])
            ->add('password', PasswordType::class, [
                'label' => 'New Password',
                'constraints' => [
                    new NotBlank(),
                    // You can add other constraints for password strength here
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Set the data class to null since this form doesn't map to an entity
            'data_class' => null,
        ]);
    }
}
