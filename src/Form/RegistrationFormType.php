<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // On ajoute les champs nécessaires à l'inscription
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email'
            ])
            ->add('username', TextType::class, [
                'label' => 'Identifiant'
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom'
            ])
          ->add('lastName', TextType::class, [
    'label' => 'Nom'
])

            ->add('plainPassword', TextType::class, [
                'label' => 'Mot de passe',
                'mapped' => false // On va l’encoder dans le contrôleur
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Le formulaire est lié à l'entité User
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
