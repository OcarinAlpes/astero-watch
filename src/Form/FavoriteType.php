<?php

namespace App\Form;

use App\Entity\Favorite;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FavoriteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('asteroidId', HiddenType::class)
            ->add('name', TextType::class, [
                'label' => 'Nom de l\'astéroïde',
                'attr' => ['readonly' => true],
            ])
            ->add('isHazardous', CheckboxType::class, [
                'label' => 'Potentiellement dangereux',
                'required' => false,
                'attr' => ['disabled' => true],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes personnelles',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Ajoute tes observations...',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Favorite::class,
        ]);
    }
}