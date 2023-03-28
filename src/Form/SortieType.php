<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Sortie;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\DateTime;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie : '
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => 'Date et heure du début de la sortie : '
            ])
            ->add('duree', ChoiceType::class, [
                'label' => 'Durée (en minutes) : ',
                'choices' => array_combine(range(30, 240, 15), range(30, 240, 15)), // de 30 à 240 minutes, par tranche de 15 minutes
                'data' => 30 // durée par défaut
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'html5' => true,
                'widget' => 'single_text',
                'label' => "Date limite de l'inscription : "
            ])
            ->add('nbInscriptionMax')
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Infos sur la sortie : '
            ])
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                    'choice_label' => 'libelle',
                    'query_builder' => function(EntityRepository $er){
                        return $er->createQueryBuilder('e')
                            ->orderBy('e.libelle', 'ASC');
                    },
                 ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
