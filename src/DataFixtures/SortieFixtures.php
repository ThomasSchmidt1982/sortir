<?php

namespace App\DataFixtures;


use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
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

       /* $sortie1 = new Sortie();
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
        $organisateur = $this->getReference('participant_3', Participant::class);
        $sortie1->setOrganisateur($organisateur);

        $participant1 = $this->getReference('participant_1', Participant::class);
        $participant2 = $this->getReference('participant_2', Participant::class);
        $sortie1->addParticipant($participant1);
        $sortie1->addParticipant($participant2);

        $manager->persist($sortie1);


        $sortie2 = new Sortie();
        $sortie2->setNom('carnaval');
        $dateDebut = $faker->dateTimeBetween('-2 years', '+2 years');
        $sortie2->setDateHeureDebut(\DateTimeImmutable::createFromMutable($dateDebut));
        $sortie2->setDuree($faker->numberBetween(1000, 10000));
        $dateLimit = $faker->dateTimeBetween('-2 years', '+2 year');
        $sortie2->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($dateLimit));
        $sortie2->setNbInscriptionMax($faker->numberBetween(10, 500));
        $sortie2->setInfosSortie($faker->text(maxNbChars: 500));
        // Ajout d'une référence pour Etat (choisie aléatoirement)
        $etats = ['En création'];
        $etatNom = $etats[array_rand($etats)];
        $sortie2->setEtat($this->getReference($etatNom, Etat::class)); // Référence définie dans EtatFixtures
        $sortie2->setLieu($this->getReference('lieu_jimi_boy', Lieu::class));
        $organisateur = $this->getReference('participant_5', Participant::class);
        $sortie2->setOrganisateur($organisateur);

        $participant3 = $this->getReference('participant_3', Participant::class);
        $participant4 = $this->getReference('participant_4', Participant::class);
        $sortie2->addParticipant($participant3);
        $sortie2->addParticipant($participant4);

        $manager->persist($sortie2);
*/

        for ($i = 1; $i <= 10; $i++) {
            $sortie = new Sortie();
            // Définition des données aléatoires pour la sortie
            $sortie->setNom($faker->sentence(3)); // Nom aléatoire
            $dateDebut = $faker->dateTimeBetween('-1 years', '+1 years');
            $sortie->setDateHeureDebut(\DateTimeImmutable::createFromMutable($dateDebut));
            $sortie->setDuree($faker->numberBetween(60, 240)); // Durée entre 1h et 4h
            $dateLimit = $faker->dateTimeBetween($dateDebut, '+1 years');
            $sortie->setDateLimiteInscription(\DateTimeImmutable::createFromMutable($dateLimit));
            $sortie->setNbInscriptionMax($faker->numberBetween(10, 50)); // Max participants
            $sortie->setInfosSortie($faker->text(200)); // Description

            // Récupération aléatoire d'un état défini dans EtatFixtures
            $etatRef = $faker->randomElement(['En création', 'Ouverte', 'Cloturée', 'En cours', 'Terminée', 'Annulée', 'Historisée' ]);
            $sortie->setEtat($this->getReference($etatRef, Etat::class));

            // Récupération aléatoire d'un lieu défini dans LieuFixtures
            $lieuRef = $faker->randomElement(['lieu_don_ricardo', 'lieu_jimi_boy', 'lieu_criss_cross', 'lieu_little_italy', 'lieu_chez_georges', 'lieu_le_moulin_vert', 'lieu_café_du_cycliste', 'lieu_la_table_ronde', 'lieu_les_deux_tours', 'lieu_le_grand_bleu' ]);
            $sortie->setLieu($this->getReference($lieuRef, Lieu::class));

            // Définition aléatoire d'un organisateur défini dans ParticipantFixtures
            $organisateurRef = $faker->randomElement(['participant_0', 'participant_1', 'participant_2', 'participant_3', 'participant_4']);
            $sortie->setOrganisateur($this->getReference($organisateurRef, Participant::class));

            $manager->persist($sortie);
        }

            $manager->flush();
    }

    public
    function getDependencies(): array
    {
        return [
            EtatFixtures::class,
            LieuFixtures::class,
            ParticipantFixtures::class,
        ];

    }

}