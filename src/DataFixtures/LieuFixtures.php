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

        $lieuxData = [
            ['nom' => 'Don Ricardo', 'rue' => 'Rue de la souris dansante', 'latitude' => 43.6047, 'longitude' => 1.4442, 'ville' => 'Toulouse'],
            ['nom' => 'Jimi Boy', 'rue' => 'Rue du pont neuf', 'latitude' => 23.6047, 'longitude' => 18.4442, 'ville' => 'Paris'],
            ['nom' => 'Criss Cross', 'rue' => 'Impasse Bourdon', 'latitude' => 3.1447, 'longitude' => 15.440, 'ville' => 'Nantes'],
            ['nom' => 'Little Italy', 'rue' => 'Avenue des étoiles', 'latitude' => 31.2447, 'longitude' => 9.5653, 'ville' => 'Lyon'],
            ['nom' => 'Chez Georges', 'rue' => 'Quai des brumes', 'latitude' => 45.7640, 'longitude' => 4.8357, 'ville' => 'Toulouse'],
            ['nom' => 'Le Moulin Vert', 'rue' => 'Boulevard Saint-Michel', 'latitude' => 48.8566, 'longitude' => 2.3522, 'ville' => 'Paris'],
            ['nom' => 'Café du Cycliste', 'rue' => 'Rue des Oliviers', 'latitude' => 43.7000, 'longitude' => 7.2600, 'ville' => 'Nantes'],
            ['nom' => 'La Table Ronde', 'rue' => 'Rue de Bretagne', 'latitude' => 48.1173, 'longitude' => -1.6778, 'ville' => 'Chartres'],
            ['nom' => 'Les Deux Tours', 'rue' => 'Allée des Cerfs', 'latitude' => 50.6292, 'longitude' => 3.0573, 'ville' => 'Lyon'],
            ['nom' => 'Le Grand Bleu', 'rue' => 'Rue des vagues', 'latitude' => 43.2965, 'longitude' => 5.3698, 'ville' => 'Toulouse'],
        ];


        foreach ($lieuxData as $index => $data) {
            $lieu = new Lieu();
            $lieu->setNom($data['nom']);
            $lieu->setRue($data['rue']);
            $lieu->setLatitude($data['latitude']);
            $lieu->setLongitude($data['longitude']);
            $lieu->setVille($this->getReference($data['ville'], Ville::class));
            $this->addReference('lieu_' . strtolower(str_replace(' ', '_', $data['nom'])), $lieu);

            $manager->persist($lieu);
        }


/*
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
*/

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