<?php

namespace App\Tests\Service;

use App\Service\Verification;
use PHPUnit\Framework\TestCase;

class VerificationTest extends TestCase
{

    # fonction de test afin de tester le bon retour de la fonction estAdulte situé dans la classe Verification
    public function testEstAdulte()
    {
        # format de date (mois/jours/année)

        # Test avec un client majeur
        $this->assertTrue(Verification::estAdulte("01/01/2001"));
        // Test avec un client qui vient de tout juste d'etre majeur (test effectué le 25 octobre 2021)
        $this->assertTrue(Verification::estAdulte("10/25/2003"));

        # Test avec un client mineur
        $this->assertFalse(Verification::estAdulte("12/25/2021"));
        $this->assertFalse(Verification::estAdulte("05/12/2005"));
    }

    # fonction de test afin de tester le bon retour de la fonction isValidSize situé dans la classe Verification
    public function testAUneTailleValide(){
        // Taille de client valide
        $this->assertTrue(Verification::aUneTailleValide("5' 7\"", 171));
        $this->assertTrue(Verification::aUneTailleValide("2' 4\"", 72));
        $this->assertTrue(Verification::aUneTailleValide("8' 7\"", 262));

        // Taille de client invalide
        $this->assertFalse(Verification::aUneTailleValide("5' 6\"", 142));
        $this->assertFalse(Verification::aUneTailleValide("2' 7\"", 1));
    }

}
