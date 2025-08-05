<?php

namespace App\DataFixtures;


use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {

        $etats = ['En création', 'Ouverte', 'Cloturée', 'En cours', 'Terminée', 'Annulée', 'Historisée' ];

        foreach($etats as $etatNom){
            $etat = new Etat();
            $etat->setLibelle($etatNom);
            $manager->persist($etat);
            $this->addReference($etatNom, $etat);
        }
        $manager->flush();
    }

    public
    function getDependencies(): array
    {
        return [
            VilleFixtures::class,
        ];
    }

}