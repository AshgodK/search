<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom')
        ->add('prenom') 
        ->add('email')
        ->add('ImagePath', FileType::class, [
            'label' => 'Image (JPG file)',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new File([
                    
                    'mimeTypesMessage' => 'Please upload a valid JPG document',
                ])
            ],
        ])
        ->add('date_n', DateType::class, [
            'widget' => 'single_text',
            
        ])
        ->add('sexe', ChoiceType::class, [
            'choices' => [
                'Male' => 'Male',
                'Femme' => 'Femme',
                'Other' => 'Other',
                
            ],
           
        ])
        ->add('adresse')
        ->add('num_tel')
        
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
