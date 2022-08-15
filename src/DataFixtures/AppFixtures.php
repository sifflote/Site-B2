<?php

namespace App\DataFixtures;

use App\Entity\B2\Config;
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
/*
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
*/
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
            ],
            22 => [
                'name' => 'T2A MGEN',
                'color' => 'dark',
                'bgcolor' => 'purple'
            ],
        ];

        foreach($observations as $observation){
            $obs = new Observations();
            $obs->setName($observation['name']);
            $obs->setColor($observation['color']);
            $obs->setBgcolor($observation['bgcolor']);

            $manager->persist($obs);
        }

        $validUnlimited = new \DateTimeImmutable('2100-01-01');
        $start =  new \DateTimeImmutable('1900-01-01');
        $psc = new \DateTimeImmutable('2022-08-03');
        $configs = [
            1 => [
                'name' => 'Type',
                'field' => 'A',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            2 => [
                'name' => 'Classe',
                'field' => 'B',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            3 => [
                'name' => 'Iep',
                'field' => 'C',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            4 => [
                'name' => 'Ipp',
                'field' => 'D',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            5 => [
                'name' => 'Facture',
                'field' => 'E',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            6 => [
                'name' => 'Name',
                'field' => 'F',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            7 => [
                'name' => 'EnterAt',
                'field' => 'G',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            8 => [
                'name' => 'ExitAt',
                'field' => 'H',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            9 => [
                'name' => 'Montant',
                'field' => 'I',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            10 => [
                'name' => 'Encaissement',
                'field' => 'J',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            11 => [
                'name' => 'Restantdu',
                'field' => 'K',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            12 => [
                'name' => 'Reference',
                'field' => 'L',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            13 => [
                'name' => 'Pec',
                'field' => 'M',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            14 => [
                'name' => 'Lot',
                'field' => 'N',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            15 => [
                'name' => 'Payeur',
                'field' => 'O',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            16 => [
                'name' => 'CodeRejet',
                'field' => 'P',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            17 => [
                'name' => 'DescRejet',
                'field' => 'Q',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            18 => [
                'name' => 'CreeAt',
                'field' => 'R',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            19 => [
                'name' => 'RejetAt',
                'field' => 'S',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            20 => [
                'name' => 'Designation',
                'field' => 'T',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            21 => [
                'name' => 'Uh',
                'field' => 'U',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            22 => [
                'name' => 'Insee',
                'field' => 'V',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            23 => [
                'name' => 'Rang',
                'field' => 'W',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            24 => [
                'name' => 'NaissanceAt',
                'field' => 'X',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            25 => [
                'name' => 'Contrat',
                'field' => 'Y',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            26 => [
                'name' => 'NaissanceHf',
                'field' => 'Z',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            27 => [
                'name' => 'Rprs',
                'field' => 'AA',
                'valid_at' => $psc,
                'begin_at' => $start
            ],
            // DATE REJET DEUXIEME
            /*27 => [
                'name' => 'date de rejet',
                'field' => 'AB',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            */
            28 => [
                'name' => 'Observation',
                'field' => 'AC',
                'valid_at' => $psc,
                'begin_at' => $start
            ],
            29 => [
                'name' => 'Precision',
                'field' => 'AD',
                'valid_at' => $psc,
                'begin_at' => $start
            ],
            30 => [
                'name' => 'TraiteAt',
                'field' => 'AE',
                'valid_at' => $psc,
                'begin_at' => $start
            ],
            31 => [
                'name' => 'Parcours',
                'field' => 'AA',
                'valid_at' => $validUnlimited,
                'begin_at' => $psc
            ],

            32 => [
                'name' => 'Rprs',
                'field' => 'AB',
                'valid_at' => $validUnlimited,
                'begin_at' => $psc
            ],
            // DATE REJET DEUXIEME
            /*27 => [
                'name' => 'date de rejet',
                'field' => 'AC',
                'valid_at' => $validUnlimited,
                'begin_at' => $start
            ],
            */
            33 => [
                'name' => 'Observation',
                'field' => 'AD',
                'valid_at' => $validUnlimited,
                'begin_at' => $psc
            ],
            34 => [
                'name' => 'Precision',
                'field' => 'AE',
                'valid_at' => $validUnlimited,
                'begin_at' => $psc
            ],
            35 => [
                'name' => 'TraiteAt',
                'field' => 'AF',
                'valid_at' => $validUnlimited,
                'begin_at' => $psc
            ],

        ];

        foreach($configs as $data){
            $config = new Config();
            $config->setName($data['name']);
            $config->setField($data['field']);
            $config->setValidAt($data['valid_at']);
            $config->setBeginAt($data['begin_at']);

            $manager->persist($config);
        }
        $manager->flush();
    }

    function lettersToNumber($letters){
        $alphabet = range('A', 'Z');
        $number = 0;

        foreach(str_split(strrev($letters)) as $key=>$char){
            $number = $number + (array_search($char,$alphabet)+1)*pow(count($alphabet),$key);
        }
        return $number;
    }
}
