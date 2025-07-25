<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['placeholder' => 'Ex: Robe satinée']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['placeholder' => 'Ajoutez une description tendance...']
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Fichier image (optionnel)',
                'required' => false,
                'mapped' => false,
            ])

->add('couleurs', ChoiceType::class, [
    'choices' => [
        'Beige' => 'beige',
        'Taupe' => 'taupe',
        'Kaki' => 'kaki',
        'Rose tendre' => 'rose tendre',
        'Vert pomme' => 'vert pomme'
    ],
    'expanded' => true,
    'multiple' => true,
    'label' => 'Couleurs disponibles'
])



->add('tailles', ChoiceType::class, [
    'choices' => [
        'XS' => 'XS',
        'S' => 'S',
        'M' => 'M',
        'L' => 'L',
        'XL' => 'XL',
        'Taille unique' => 'Taille unique'
    ],
    'expanded' => true,
    'multiple' => true,
    'label' => 'Tailles disponibles'
])







            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'label' => 'Catégorie du produit',
                'placeholder' => 'Choisissez une catégorie',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
