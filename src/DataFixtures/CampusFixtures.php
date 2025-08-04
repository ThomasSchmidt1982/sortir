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

        //création des Campus
        $campus1 = new Campus();
        $campus1->setNom('SAINT-HERBLAIN');
        $this->addReference('SH', $campus1);
        $manager->persist($campus1);

        $campus2 = new Campus();
        $campus2->setNom('CHARTRES DE BRETAGNE');
        $this->addReference('CDB', $campus2);
        $manager->persist($campus2);

        $campus3 = new Campus();
        $campus3->setNom('LA ROCHE SUR YON');
        $this->addReference('LRSY', $campus3);
        $manager->persist($campus3);

        $campus4 = new Campus();
        $campus4->setNom('NIORT');
        $this->addReference('N', $campus4);
        $manager->persist($campus4);

        $manager->flush();
    }
}
