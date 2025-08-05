<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class VilleFixtures extends Fixture
{
    public function __construct()
    {
    }

    public function load(ObjectManager $manager): void
    {

        $faker = \Faker\Factory::create('fr_FR');

        $VILLES = ["Toulouse", "Chartres", "Paris", "Lyon", "Nantes",];
        $CODES_POSTAUX = ["31000", "28000", "75000", "69000", "44000"];


        //création de 5 Villes
        for($i=0; $i<count($VILLES); $i++){
            $ville = new Ville();
            $ville->setNom($VILLES[$i]);
            $ville->setCodePostal($CODES_POSTAUX[$i]);
            $manager->persist($ville);

            $this->addReference($VILLES[$i], $ville);

        }

        $manager->flush();
    }
}
