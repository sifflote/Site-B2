<?php

namespace App\DataFixtures;

use App\Entity\B2\Observations;
use App\Entity\Users;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ){}

    public function load(ObjectManager $manager): void
    {

        $admin = new Users();
        $admin->setEmail('admin@sifflote.fr');
        $admin->setUsername('Admin');
        $admin->setB2RejetsPerPage(500);
        $admin->setIsVerified(1);
        $admin->setMdpUse(1);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'password')
        );
        $admin->setRoles(['ROLE_USER', 'ROLE_B2', 'ROLE_ADMIN']);
        $manager->persist($admin);

        $faker = Faker\Factory::create('fr_FR');

        for($usr = 1; $usr <= 10; $usr++){
            $user = new Users();
            $user->setEmail($faker->email);
            $user->setUsername($faker->userName);
            $user->setB2RejetsPerPage(500);
            $user->setRoles(['ROLE_USER']);
            $user->setMdpUse(true);
            $user->setPlainpassword('secret');

            $manager->persist($user);
        }

        $observations = [
            1 => [
                'name' => 'ANNULATION / REFACTURATION',
                'color' => 'light',
                'bgcolor' => 'primary'
            ],
            2 => [
                'name' => 'DEMANDE DE PAIEMENT MANUEL',
                'color' => 'light',
                'bgcolor' => 'success'
            ],
            3 => [
                'name' => 'FACTURATION CORRECTE',
                'color' => 'light',
                'bgcolor' => 'rose'
            ],
            4 => [
                'name' => 'MED ATTENTE UCD',
                'color' => 'dark',
                'bgcolor' => 'info'
            ],
            5 => [
                'name' => 'ATTENTE DE PEC',
                'color' => 'dark',
                'bgcolor' => 'light'
            ],
            6 => [
                'name' => 'EXTRACTION PRECEDENTE',
                'color' => '',
                'bgcolor' => 'outline-danger'
            ],
            7 => [
                'name' => 'FORCLUSION',
                'color' => 'warning',
                'bgcolor' => 'dark'
            ],
            8 => [
                'name' => 'FPU',
                'color' => 'secondary',
                'bgcolor' => 'info'
            ],
            9 => [
                'name' => 'NOUVEAU',
                'color' => '',
                'bgcolor' => 'outline-danger'
            ],
            10 => [
                'name' => 'PROBLEME DOSSIER',
                'color' => 'danger',
                'bgcolor' => 'warning'
            ],
            11 => [
                'name' => 'RI FS',
                'color' => 'dark',
                'bgcolor' => 'warning'
            ],
            12 => [
                'name' => 'RI TE',
                'color' => 'secondary',
                'bgcolor' => 'warning'
            ],
            13 => [
                'name' => 'SOLDE',
                'color' => 'primary',
                'bgcolor' => 'info'
            ],
            14 => [
                'name' => 'SOLDE PARTIELLEMENT',
                'color' => 'secondary',
                'bgcolor' => 'info'
            ],
            15 => [
                'name' => 'TAUX ERRONE',
                'color' => 'light',
                'bgcolor' => 'purple'
            ],
            16 => [
                'name' => 'DMT INCONNU',
                'color' => '',
                'bgcolor' => 'outline-warning'
            ],
            17 => [
                'name' => 'DETENU',
                'color' => 'dark',
                'bgcolor' => 'light'
            ],
            18 => [
                'name' => 'PROBLEME ACTE',
                'color' => 'dark',
                'bgcolor' => 'warning'
            ],
            19 => [
                'name' => 'AUTRE...',
                'color' => 'dark',
                'bgcolor' => 'outline-secondary'
            ],
            20 => [
                'name' => 'TNJP',
                'color' => 'dark',
                'bgcolor' => 'purple'
            ],
            21 => [
                'name' => 'DATE DE NAISSANCE DIF',
                'color' => 'rose',
                'bgcolor' => 'purple'
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
