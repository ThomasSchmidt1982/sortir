<?php

namespace App\DataFixtures;


use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LieuFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {

        $lieu1 = new Lieu();
        $lieu1->setNom('Don Ricardo');
        $lieu1->setRue('Rue de la souris dansante');
        $lieu1->setLatitude(43.6047);
        $lieu1->setLongitude(1.4442);
        $lieu1->setVille($this->getReference('Toulouse', Ville::class));
        $this->addReference('lieu_don_ricardo', $lieu1 );

        $manager->persist($lieu1);


        $lieu2 = new Lieu();
        $lieu2->setNom('Jimi boy');
        $lieu2->setRue('Rue du pont neuf');
        $lieu2->setLatitude(23.6047);
        $lieu2->setLongitude(18.4442);
        $lieu2->setVille($this->getReference('Paris', Ville::class));
        $this->addReference('lieu_jimi_boy', $lieu2 );

        $manager->persist($lieu2);


        $lieu3 = new Lieu();
        $lieu3->setNom('Criss Cross');
        $lieu3->setRue('impasse bourdon');
        $lieu3->setLatitude(3.1447);
        $lieu3->setLongitude(15.440);
        $lieu3->setVille($this->getReference('Nantes', Ville::class));
        $this->addReference('lieu_criss_cross', $lieu3 );

        $manager->persist($lieu3);

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