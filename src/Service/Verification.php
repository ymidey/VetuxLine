<?php

namespace App\Service;

class Verification
{
    /**
     * @param $dateCli
     * @return bool
     */
    public static function estAdulte($dateCli): bool
    {
        # Déclaration de la variable $dateClient avec comme valeur le retour du constructeur DateTime avec comme paramètre $dateCli
        $dateClient = new \DateTime($dateCli);
        # Déclaration de la variable $today avec comme valeur le retour du constructeur DateTime avec comme paramètre 'now'
        $today = new \DateTime('now');
        # Déclaration de la variable $diff avec comme valeur le retour de la fonction DateTime::diff avec comme paramètre la variable $today et $dateClient
        $diff = $today->diff($dateClient);
        # On transforme la valeur contenue dans la variable $diff en âge puis, on la donne comme argument à la variable $age
        $age = $diff->y;
        # On vérifie si la valeur contenue dans la variable $age est supérieur ou égal à 18
        if ($age >= 18) {
            # Si, la valeur est bien supérieure ou égal, on retourne vrai (true)
            return true;
        }
        # Sinon, on retourne faux (false)
        return false;
    }

    /**
     * @param $inch
     * @param $cm
     * @return bool
     */
    public static function aUneTailleValide($inch, $cm): bool
    {
        # Déclaration de la variable $inches avec comme valeur le retour du calcul (donnée dans la variable $cm / 2.54)
        $inches = $cm / 2.54;
        # Déclaration de la variable $feet avec comme valeur le retour du calcul (donnée dans la variable $inches / 12) arrondie à une valeur numérique entière
        $feet = intval($inches / 12);
        $inches = $inches % 12;
        return sprintf("%d" . "' " . "%d" . '"', $feet, $inches) === $inch;
    }

}