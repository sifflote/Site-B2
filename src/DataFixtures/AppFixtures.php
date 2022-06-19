<?php

namespace App\DataFixtures;

use App\Entity\B2\Observations;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Faker;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ){}

    public function load(ObjectManager $manager): void
    {
        $users = [];

        $admin = new Users();
        $admin->setEmail('siffli13@gmail.com');
        $admin->setFullname('Administrateur');
        $admin->setUsername('admin');
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'password')
        );
        $admin->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
        $manager->persist($admin);


        $faker = Faker\Factory::create('fr_FR');

        for($usr = 1; $usr <= 10; $usr++){
            $user = new Users();
            $user->setEmail($faker->email);
            $user->setUsername($faker->userName);
            $user->setFullname($faker->name);
            $user->setRoles(['ROLE_USER']);
            $user->setPlainpassword('secret');

            $users[] = $user;
            $manager->persist($user);
        }

        $observations = [
            1 => [
                'name' => 'ANNULATION / REFACTURATION',
                'color' => '',
                'bgcolor' => ''
            ],
            2 => [
                'name' => 'DEMANDE DE PAIEMENT MANUEL',
                'color' => '',
                'bgcolor' => ''
            ],
            3 => [
                'name' => 'FACTURATION CORRECTE',
                'color' => '',
                'bgcolor' => ''
            ],
            4 => [
                'name' => 'MED ATTENTE UCD',
                'color' => '',
                'bgcolor' => ''
            ],
            5 => [
                'name' => 'ATTENTE DE PEC',
                'color' => '',
                'bgcolor' => ''
            ],
            6 => [
                'name' => 'EXTRACTION PRECEDENTE',
                'color' => '',
                'bgcolor' => ''
            ],
            7 => [
                'name' => 'FORCLUSION',
                'color' => '',
                'bgcolor' => ''
            ],
            8 => [
                'name' => 'FPU',
                'color' => '',
                'bgcolor' => ''
            ],
            9 => [
                'name' => 'NOUVEAU',
                'color' => '',
                'bgcolor' => ''
            ],
            10 => [
                'name' => 'PROBLEME DOSSIER',
                'color' => '',
                'bgcolor' => ''
            ],
            11 => [
                'name' => 'RI FS',
                'color' => '',
                'bgcolor' => ''
            ],
            12 => [
                'name' => 'RI TE',
                'color' => '',
                'bgcolor' => ''
            ],
            13 => [
                'name' => 'SOLDE',
                'color' => '',
                'bgcolor' => ''
            ],
            14 => [
                'name' => 'SOLDE PARTIELLEMENT',
                'color' => '',
                'bgcolor' => ''
            ],
            15 => [
                'name' => 'TAUX ERRONE',
                'color' => '',
                'bgcolor' => ''
            ],
            16 => [
                'name' => 'DMT INCONNU',
                'color' => '',
                'bgcolor' => ''
            ],
            17 => [
                'name' => 'DETENU',
                'color' => '',
                'bgcolor' => ''
            ],
            18 => [
                'name' => 'PROBLEME ACTE',
                'color' => '',
                'bgcolor' => ''
            ],
            19 => [
                'name' => 'AUTRE...',
                'color' => '',
                'bgcolor' => ''
            ],
            20 => [
                'name' => 'TNJP',
                'color' => '',
                'bgcolor' => ''
            ],
            21 => [
                'name' => 'DATE DE NAISSANCE DIF',
                'color' => '',
                'bgcolor' => ''
            ]
        ];

        foreach($observations as $observation){
            $obs = new Observations();
            $obs->setName($observation['name']);
            $obs->setColor($observation['color']);
            $obs->setBgcolor($observation['bgcolor']);

            $manager->persist($obs);
        }

        $manager->flush();
    }
}
