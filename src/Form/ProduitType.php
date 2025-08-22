<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
            ->add('images', FileType::class, [
                'label' => 'Images',
                'multiple' => true,
                'mapped' => false,
                'required' => false,
            ])
            ->add('couleurs', TextType::class, [
                'label' => 'Couleurs disponibles',
                'attr' => [
                    'placeholder' => 'Ex: Rouge, Bleu, Vert (séparez par des virgules)',
                    'help' => 'Écrivez les couleurs disponibles séparées par des virgules'
                ],
                'mapped' => false,
                'required' => false
            ])
            ->add('tailles', ChoiceType::class, [
                'choices' => [
                    'Tailles adultes' => [
                        'XS' => 'XS',
                        'S' => 'S',
                        'M' => 'M',
                        'L' => 'L',
                        'XL' => 'XL',
                        'XXL' => 'XXL',
                        'Taille unique' => 'Taille unique'
                    ],
                    'Tailles enfants' => [
                        '2 ans' => '2 ans',
                        '3 ans' => '3 ans',
                        '4 ans' => '4 ans',
                        '5 ans' => '5 ans',
                        '6 ans' => '6 ans',
                        '7 ans' => '7 ans',
                        '8 ans' => '8 ans',
                        '9 ans' => '9 ans',
                        '10 ans' => '10 ans',
                        '11 ans' => '11 ans',
                        '12 ans' => '12 ans',
                        '13 ans' => '13 ans',
                        '14 ans' => '14 ans',
                        '15 ans' => '15 ans',
                        '16 ans' => '16 ans'
                    ]
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
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-lg btn-primary w-100'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}

