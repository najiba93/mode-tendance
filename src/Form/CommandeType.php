<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom complet',
                'attr' => [
                    'placeholder' => 'Votre nom complet',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nom est obligatoire'
                    ])
                ]
            ])
            ->add('adressePostale', TextareaType::class, [
                'label' => 'Adresse de facturation',
                'attr' => [
                    'placeholder' => 'Votre adresse complète',
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'adresse est obligatoire'
                    ])
                ]
            ])
            ->add('adresseLivraison', TextareaType::class, [
                'label' => 'Adresse de livraison (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Adresse de livraison différente (laisser vide si identique)',
                    'class' => 'form-control',
                    'rows' => 3
                ]
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => 'Votre numéro de téléphone',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le téléphone est obligatoire'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[0-9+\-\s\(\)]{10,}$/',
                        'message' => 'Veuillez entrer un numéro de téléphone valide'
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Finaliser ma commande',
                'attr' => [
                    'class' => 'btn btn-success btn-lg w-100'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
} 