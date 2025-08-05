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
        $admin->setCampus($this->getReference('LRSY', Campus::class));
        $manager->persist($admin);

        // création de 1 user actif
        $user1 = new Participant();
        $user1->setNom('User');
        $user1->setPrenom('actif');
        $user1->setPseudo('ua');
        $user1->setMail('actif.user@sortir.fr');
        $user1->setMotPasse($this->passwordHasher->hashPassword($user1,'123456'));
        $user1->setTelephone('1234567891');
        $user1->setActif(true);
        $user1->setAdministrateur(false);
        $user1->setCampus($this->getReference('SH', Campus::class));
        $manager->persist($user1);

        // création de 1 user INACTIF
        $user2 = new Participant();
        $user2->setNom('User');
        $user2->setPrenom('inactif');
        $user2->setPseudo('ui');
        $user2->setMail('inactif.user@sortir.fr');
        $user2->setMotPasse($this->passwordHasher->hashPassword($user2,'123456'));
        $user2->setTelephone('1234567891');
        $user2->setActif(false);
        $user2->setAdministrateur(false);
        $user2->setCampus($this->getReference('N', Campus::class));

        $manager->persist($user2);


        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
        ];
    }
}
