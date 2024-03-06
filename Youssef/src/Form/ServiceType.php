<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use App\Entity\Service;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('user', EntityType::class, [
            'class' => 'App\Entity\User',
            'choice_label' => 'username', 
        ])
            ->add('nom')
            ->add('description')
            ->add('disponibilite')
            ->add('categorie')
            ->add('image', FileType::class, [
                'label' => 'Image',
                'mapped' => false, // This field is not mapped to any property
                'required' => true,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
