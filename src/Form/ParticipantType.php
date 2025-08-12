<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAdmin = $options['isAdmin'];
        $disabledCampus = true;
        if ($isAdmin) {
            $disabledCampus = false;
        }

        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('pseudo' , TextType::class, [
                'label' => 'pseudo',
            ])
            ->add('telephone', TextType::class, [
                'label' => 'telephone',
                'required' => false,
            ])
            ->add('mail', EmailType::class, [
                'label' => 'mail',
            ])
            ->add('motPasse', PasswordType::class, [
                'label' => 'mot de passe',
                'required' => false,
                'mapped' => false,
                'attr'=> [
                    'autocomplete' => 'new-password'
                ]
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'disabled' => $disabledCampus, // Rendre le champ non modifiable pour user
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'isAdmin' => false,
        ]);
    }
}
