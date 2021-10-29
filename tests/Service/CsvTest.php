<?php


namespace App\Tests\Service;

use App\Service\Csv;
use App\Service\Merge;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{

    protected $frenchDataCsv;

    protected $germanDataCsv;

    protected $invalidHeader;

    protected function setUp(): void
    {
        $this->frenchDataCsv = __DIR__ . "tests/../data/small-french-client.csv";
        $this->germanDataCsv = __DIR__ . "tests/../data/small-german-client.csv";
        $this->invalidHeader = __DIR__ . "tests/../data/invalid-header.csv";
    }

    # fonction pour tester le bon retour des valeurs de la fonction getSpecificColums situé dans la classe Csv
    public function testGetColumns()
    {
        # On instancie les données de la fonction getSpecificColums dans une variable nommé colums
        $columns = Csv::getSpecificColumns();
        # Bonne valeur
        $this->assertEquals($columns[0], "Gender");
        $this->assertEquals($columns[\count($columns) - 1], "Longitude");
        # Mauvaise valeur
        $this->assertNotEquals($columns[5], "Colonne 5");
        $this->assertNotEquals($columns[7], "Je suis une colonne");
    }

    # fonction pour tester le bon retour des valeurs de la fonction getSpecificColums situé dans la classe Csv mais en fonction de la clé
    public function testArrayKeyExist()
    {
        # On instancie les données de la fonction getSpecificColums dans une variable nommé colums
        $columns = Csv::getSpecificColumns();
        # La clé donnée existe
        $this->assertArrayHasKey("0", $columns);
        $this->assertArrayHasKey(count($columns) - 1, $columns);
        # La clé donnée n'existe pas
        $this->assertArrayNotHasKey("65", $columns);
        $this->assertArrayNotHasKey("une clé invalide", $columns);
    }

    # fonction de test afin de tester le retour de la fonction getCsvHeader avec les données de frenchDataCsv situé dans la classe Csv
    public function testGetCsvHeader()
    {
        # On instancie les données de la fonction getCsvHeader avec comme paramètre frenchDataCsv dans une variable nommé csvHeader
        $csvHeader = Csv::getCsvHeader($this->frenchDataCsv);
        # Bonne valeur
        $this->assertEquals($csvHeader[0], "Number");
        # Mauvaise valeur
        $this->assertNotEquals($csvHeader[2], "CcNumber");
    }

    # fonction de test afin de tester le retour de la fonction getCsvHeader avec les valeurs de germanDataCsv situé dans la classe Csv
    public function testIsValidCsvHeader()
    {
        # On instancie les données de la fonction getCsvHeader avec comme paramètre germanDataCsv dans une variable nommé csvHeader
        $csvHeader = Csv::getCsvHeader($this->germanDataCsv);
        $this->assertTrue(Csv::isValidCsvHeader($csvHeader));
        # On instancie les données de la fonction getCsvHeader avec comme paramètre invalidHeader dans une variable nommé csvHeader
        $csvHeader = Csv::getCsvHeader($this->invalidHeader);
        $this->assertFalse(Csv::isValidCsvHeader($csvHeader));
    }

    # fonction de test afin de tester le bon retour de la fonction getArrayOfCsvCcNumber situé dans la classe Csv
    public function testGetArrayOfCsvCcNumber()
    {
        # On instancie les données du constructeur merge avec comme paramètre germanDataCsv et frenchDataCsv dans une variable nommé merge
        $merge = new Merge($this->frenchDataCsv, $this->germanDataCsv);
        # On instancie les données de la fonction getArrayOfCsvCcNumber avec comme paramètre la valeur csv1 du constructeur merge dans une variable nommé arrayOfCsvCcNumber
        $arrayOfCsvCcNumber = Csv::getArrayOfCsvCcNumber($merge->getCsv1()->getRecords());
        # Teste avec les valeurs d'un seul fichier Csv dans un tableau
        # Bonne valeur
        $this->assertEquals($arrayOfCsvCcNumber[0], 4532650833355085);
        $this->assertEquals($arrayOfCsvCcNumber[8], 5541968545848197);
        $this->assertEquals($arrayOfCsvCcNumber[10], 5442377081429786);
        $this->assertEquals($arrayOfCsvCcNumber[11], 5551781297657261);
        # Mauvaise valeur
        $this->assertNotEquals($arrayOfCsvCcNumber[0], 4532656833355085);
        $this->assertNotEquals($arrayOfCsvCcNumber[3], 4532650833359235);

        # Teste avec les valeurs de deux fichiers Csv dans un tableau
        $arrayOfCsvCcNumber = Csv::getArrayOfCsvCcNumber2Csv($merge->getCsv1()->getRecords(), $merge->getCsv2()->getRecords());
        # Bonne valeur
        $this->assertEquals($arrayOfCsvCcNumber[0], 4532650833355085);
        $this->assertEquals($arrayOfCsvCcNumber[11], 5551781297657261);
        $this->assertEquals($arrayOfCsvCcNumber[12], 4539448007296299);
        $this->assertEquals($arrayOfCsvCcNumber[15], 4556719654529630);
        $this->assertEquals($arrayOfCsvCcNumber[19], 4916323840781851);
        # Mauvaise valeur
        $this->assertNotEquals($arrayOfCsvCcNumber[4], 4532650833855085);
        $this->assertNotEquals($arrayOfCsvCcNumber[17], 4854650833855085);
    }

    # fonction de test afin de tester le bon retour de la fonction getDuplicateValueInArray situé dans la classe Csv
    public function testDuplicateValueInArray()
    {
        // Tableau avec des duplications de valeurs (valeur 1 et 2)
        $array = [1, 2, 1, 3, 2, 2];
        $this->assertEquals([1, 2], Csv::getDuplicateValueInArray($array));

        // Tableau sans duplication de valeur
        $array = [123, 7, 19, 666];
        $this->assertEquals([], Csv::getDuplicateValueInArray($array));
    }
}