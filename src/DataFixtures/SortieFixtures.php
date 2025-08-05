<?php

namespace App\DataFixtures;


use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_FR');

        $sortie1 = new Sortie();
        $sortie1->setNom('nouvel an');
        $dateDebut = $faker->dateTimeBetween('-2 years', '+2 years');
        $sortie1->setDateHeureDebut(\DateTimeImmutable::createFromMutable($dateDebut));
        $sortie1->setDuree($faker->numberBetween(1000, 10000));
        $dateLimit = $faker->dateTimeBetween('-2 years', '+2 year');
        $sortie1->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($dateLimit));
        $sortie1->setNbInscriptionMax($faker->numberBetween(10, 500));
        $sortie1->setInfosSortie($faker->text(maxNbChars: 500));

        // Ajout d'une référence pour Etat (choisie aléatoirement)
        $etats = ['En création'];
        $etatNom = $etats[array_rand($etats)];
        $sortie1->setEtat($this->getReference($etatNom, Etat::class)); // Référence définie dans EtatFixtures

        $sortie1->setLieu($this->getReference('lieu_don_ricardo', Lieu::class));

        $manager->persist($sortie1);

        $manager->flush();
    }

    public
    function getDependencies(): array
    {
        return [
            EtatFixtures::class,
            LieuFixtures::class,
        ];

    }

}