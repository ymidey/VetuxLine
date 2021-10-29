<?php

# Nom du package
namespace App\Service;

# Importation des classes nécessaires
use League\Csv\Reader;
use League\Csv\Writer;

# Nom de la classe
class Invalid
{
    # Déclaration des attributs
    private $clientAjouter;

    private $columns;

    private $csv;

    private $mergeCsvInvalide;

    private $type;

    private $arrayOfCsvCcNumber;

    /**
     * Constructeur de la classe Invalid avec comme paramètre $csvData et $type
     * @param $csvData
     */
    public function __construct($csvData, $type)
    {
        # Ajout des arguments aux différents attributs
        $this->columns = array_map('strtolower', Csv::getSpecificColumns());
        $this->csv = Reader::createFromPath($csvData)->setHeaderOffset(0);
        $this->csv = Reader::createFromPath($csvData)->setHeaderOffset(0);
        $this->mergeCsvInvalide = Writer::createFromFileObject(new \SplTempFileObject());
        $this->mergeCsvInvalide->insertOne(Csv::getSpecificColumns());
        $this->clientAjouter = 0;
        $this->type = $type;
        $this->arrayOfCsvCcNumber = csv::getArrayOfCsvCcNumber($this->csv->getRecords());
    }

    /**
     * @param $csvData
     */
    private function insertIntoCsv($csvData)
    {
        # On lit chaque valeur du tableau envoyé en paramètre ($csvData)
        foreach ($csvData as $data) {
            $data = array_change_key_case($data, CASE_LOWER);
            # Déclaration du tableau $content
            $content = [];
            # On lit chaque valeur du tableau $column
            foreach ($this->columns as $column) {
                # Chaque valeur lut est ajouté au tableau $content
                $content[] = $data[$column];
            }
            # On vérifie si la valeur de l'attribut type est égal à 'allClient'
            if ($this->type == "allClient") {
                # On vérifie si le client n'est pas adulte avec la fonction estAdulte, s'il n'a pas une taille valide avec la fonction aUneTailleValide
                # et si le code de sa carte de crédit est en doublon avec un autre client avec la fonction getDuplicateValueInArray
                if (!Verification::estAdulte($data["birthday"])|| !Verification::aUneTailleValide($data["feetinches"], $data["centimeters"]) || in_array($data["ccnumber"], Csv::getDuplicateValueInArray($this->arrayOfCsvCcNumber))) {
                    # Si le client, réussi à passer tous ses tests, il est ajouté avec la fonction insertOne de la librairie PHP-League/CSV au fichier de fusion mergeCsv
                    $this->mergeCsvInvalide->insertOne($content);
                    # Un client est ajouté, la valeur de l'attribut clientAjouter passe donc a 1
                    $this->clientAjouter = 1;
                }
            }
            # On vérifie si la valeur de l'attribut type est égal à 'notMajor'
            elseif ($this->type == "notMajor") {
                # On vérifie si le client n'est pas adulte avec la fonction estAdulte provenant de la classe Verification
                if (!Verification::estAdulte($data["birthday"])) {
                    # Si le client, réussi à passer le test, il est ajouté avec la fonction insertOne de la librairie PHP-League/CSV au fichier de fusion mergeCsv
                    $this->mergeCsvInvalide->insertOne($content);
                    # Un client est ajouté, la valeur de l'attribut clientAjouter passe donc a 1
                    $this->clientAjouter = 1;
                }
            }
            # On vérifie si la valeur de l'attribut type est égal à 'invalidSize'
            elseif ($this->type == "invalidSize") {
                # On vérifie si le client possède bien une taille en cm et en feetinch invalide avec la fonction aUneTailleValide provenant de la classe Verification
                if (!Verification::aUneTailleValide($data["feetinches"], $data["centimeters"])) {
                    # Si le client, réussi à passer le test, il est ajouté avec la fonction insertOne de la librairie PHP-League/CSV au fichier de fusion mergeCsv
                    $this->mergeCsvInvalide->insertOne($content);
                    # Un client est ajouté, la valeur de l'attribut clientAjouter passe donc a 1
                    $this->clientAjouter = 1;
                }
            }
            # On vérifie si la valeur de l'attribut type est égal à 'invalidCcNumber'
            elseif ($this->type == "invalidCcNumber") {
                # On vérifie si le client possède un code de carte de crédit en doublon avec un autre client avec la fonction getDuplicateValueInArray provenant de la classe Csv
                if (in_array($data["ccnumber"], Csv::getDuplicateValueInArray($this->arrayOfCsvCcNumber))) {
                    # Si le client, réussi à passer le test, il est ajouté avec la fonction insertOne de la librairie PHP-League/CSV au fichier de fusion mergeCsv
                    $this->mergeCsvInvalide->insertOne($content);
                    # Un client est ajouté, la valeur de l'attribut clientAjouter passe donc a 1
                    $this->clientAjouter = 1;
                }
            }
        }
    }

    public function fusion(): bool
    {
        # On appelle la fonction insertIntoCsvForSequential en passent comme argument csv transformé en tableau grâce à la fonction
        # getRecords de la librairie PHP-League/CSV
        $this->insertIntoCsv($this->csv->getRecords());
        # On retourne un booléen, true si au moins un client a été ajouté, false si aucun n'a été ajouté
        return $this->clientAjouter === 1;
    }

    /**
     * @param string $csvName
     */
    public
    function downloadCsv(string $csvName = 'client-invalide-')
    {
        # On retourne les valeurs de l'attribut mergeCsvInvalide avec la fonction output de la librairie
        # PHP-League/Csv en lui donner comme paramètre (comme nom), la valeur de la variable $nomCsv
        # + la date d'aujourd'hui en format (Y-m-d) avec la fonction date puis .csv afin de lui donner
        # le format csv au téléchargement
        return $this->mergeCsvInvalide->output($csvName.$this->type.date('Y-m-d').'.csv');

    }
}