<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {


        $faker = \Faker\Factory::create('fr_FR');

        // création d'un Admin
        $admin = new Participant();
        $admin->setNom('Dupont');
        $admin->setPrenom('Gérard');
        $admin->setPseudo('gg');
        $admin->setMail('gerard.dupont@sortir.fr');
        $admin->setMotPasse($this->passwordHasher->hashPassword($admin,'123456'));
        $admin->setTelephone('0123456789');
        $admin->setActif(true);
        $admin->setAdministrateur(true);
        $admin->setCampus($this->getReference('Niort', Campus::class));

        $manager->persist($admin);

        // création de 1 user INACTIF
//        $user2 = new Participant();
//        $user2->setNom('User');
//        $user2->setPrenom('inactif');
//        $user2->setPseudo('ui');
//        $user2->setMail('inactif.user@sortir.fr');
//        $user2->setMotPasse($this->passwordHasher->hashPassword($user2,'123456'));
//        $user2->setTelephone('1234567891');
//        $user2->setActif(false);
//        $user2->setAdministrateur(false);
//        $user2->setCampus($this->getReference('Paris', Campus::class));
//
//        $manager->persist($user2);


        //ajout de 25 participants
        for($i=0; $i<25; $i++)
        {
            $user = new Participant();
            $user->setNom($faker->lastName);
            $user->setPrenom($faker->firstName);
            $user->setPseudo($user->getNom().$faker->randomNumber());
            $user->setMail($user->getPrenom().$user->getNom().'@sortir.fr');
            $user->setMotPasse($this->passwordHasher->hashPassword($user,'123456'));
            $user->setTelephone(substr(preg_replace('/[^0-9]/', '', $faker->phoneNumber), 0, 10));
            $user->setActif(rand(0,1));
            $user->setAdministrateur(false);
            $campusRef = $faker->randomElement(['Niort', 'Nantes', 'Paris', 'La Roche s/Yon', 'Toulouse',]);
            $user->setCampus($this->getReference($campusRef, Campus::class));
            $this->addReference('participant_'.$i, $user );

            $manager->persist($user);

        }




        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
        ];
    }
}
