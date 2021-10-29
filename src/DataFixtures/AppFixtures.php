<?php

# Nom du package
namespace App\DataFixtures;

# Importation des classes nécessaires
use App\Entity\Admin;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

# Nom de la classe
class AppFixtures extends Fixture
{
    # Déclaration des attributs
    private $passwordEncoder;

    # On encode l'attribut passwordEncorder avec un système de hachage provenant
    # de la classe UserPasswordEncoderInterface de la librairie doctrine
    public function __construct(UserPasswordEncoderInterface $passwordEncoder){
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        # On appelle la classe Admin situé dans App\Entity\Admin
        $user = new Admin();
        # Appel de la méthode setUsername avec en paramètre la chaine de
        # caractère root de l'objet référencé par user
        $user->setUsername("root");
        # Appel de la méthode setPassword avec en paramètre l'attribut passwordEncoder et avec comme paramètre la fonction encodePassword($user, "sio"));
        $user->setPassword($this->passwordEncoder->encodePassword($user, "sio"));
        # On notifie doctrine avec la méthode persist en lui disant que
        # l'on voudrait ajouter les valeurs que contient la variable $user
        # dans la base de données
        $manager->persist($user);
        # Avec la méthode flush de doctrine, on met à jour notre base de données
        $manager->flush();
    }
}