<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // 🧾 Champ pour le nom de la catégorie
            ->add('nom', TextType::class, [
                'label' => 'Nom de la catégorie',
                'attr' => ['placeholder' => 'Ex: Vêtements']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 🎯 Ce formulaire est lié à l’entité Categorie
            'data_class' => Categorie::class,
        ]);
    }
}
