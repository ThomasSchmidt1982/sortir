<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function __construct()
    {
    }

    public function load(ObjectManager $manager): void
    {

        $faker = \Faker\Factory::create('fr_FR');



        $campusList = ['Niort', 'Nantes', 'Paris', 'La Roche s/Yon', 'Toulouse',];

        foreach($campusList as $CampusNom){
            $campus = new Campus();
            $campus->setNom($CampusNom);
            $manager->persist($campus);
            $this->addReference($CampusNom, $campus);
        }
        $manager->flush();


        /*
        //création des Campus
        $campus1 = new Campus();
        $campus1->setNom('SAINT-HERBLAIN');
        $this->addReference('SH', $campus1);
        $manager->persist($campus1);

        $campus2 = new Campus();
        $campus2->setNom('CHARTRES');
        $this->addReference('C', $campus2);
        $manager->persist($campus2);

        $campus3 = new Campus();
        $campus3->setNom('LA ROCHE SUR YON');
        $this->addReference('LRSY', $campus3);
        $manager->persist($campus3);

        $campus4 = new Campus();
        $campus4->setNom('NIORT');
        $this->addReference('NI', $campus4);
        $manager->persist($campus4);

        $campus5 = new Campus();
        $campus5->setNom('NANTES');
        $this->addReference('NA', $campus4);
        $manager->persist($campus5);

        $campus6 = new Campus();
        $campus6->setNom('LYON');
        $this->addReference('L', $campus4);
        $manager->persist($campus4);
*/


    }
}
