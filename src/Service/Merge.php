<?php

# Nom du package
namespace App\Service;

# Importation des classes
use League\Csv\AbstractCsv;
use League\Csv\Reader;
use League\Csv\Writer;

# Nom de la classe
class Merge
{
    # Déclaration des attributs
    private $clientAjouter;

    private $columns;

    private $csv1;

    private $csv2;

    private $mergeCsv;

    private $arrayOfCsvCcNumber;

    /**
     * Constructeur de la classe Merge avec comme paramètre $csv1Data et $csv2Data
     * @param $csv1Data
     * @param $csv2Data
     */
    public function __construct($csv1Data, $csv2Data)
    {
        # Ajout des arguments aux différents attributs
        $this->columns = array_map('strtolower', Csv::getSpecificColumns());
        $this->csv1 = Reader::createFromPath($csv1Data)->setHeaderOffset(0);
        $this->csv2 = Reader::createFromPath($csv2Data)->setHeaderOffset(0);
        $this->mergeCsv = Writer::createFromFileObject(new \SplTempFileObject());
        $this->mergeCsv->insertOne(Csv::getSpecificColumns());
        $this->clientAjouter = 0;
        $this->arrayOfCsvCcNumber = Csv::getArrayOfCsvCcNumber2Csv($this->csv1->getRecords(), $this->csv2->getRecords());
    }

    /**
     * @param $csvData
     */
    private function insertIntoCsvForSequential($csvData)
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

            # On vérifie si le client est adulte avec la fonction estAdulte, s'il a une taille valide avec la fonction aUneTailleValide
            # et si le code de sa carte de crédit est unique avec la fonction getDuplicateValueInArray
            if (Verification::estAdulte($data["birthday"]) && Verification::aUneTailleValide($data["feetinches"], $data["centimeters"]) && !in_array($data["ccnumber"], Csv::getDuplicateValueInArray($this->arrayOfCsvCcNumber))) {
                # Si le client, réussi à passer tous ses tests, il est ajouté avec la fonction insertOne de la librairie PHP-League/CSV au fichier de fusion mergeCsv
                $this->mergeCsv->insertOne($content);
                # Un client est ajouté, la valeur de l'attribut clientAjouter passe donc a 1
                $this->clientAjouter = 1;
            }
        }
    }

    /**
     * @return bool
     */
    public function sequential(): bool
    {
        # On appelle la fonction insertIntoCsvForSequential en passent comme argument csv1 transformé en tableau grâce à la fonction
        # getRecords de la librairie PHP-League/CSV
        $this->insertIntoCsvForSequential($this->csv1->getRecords());
        # On appelle la fonction insertIntoCsvForSequential en passent comme argument csv2 transformé en tableau grâce à la fonction
        # getRecords de la librairie PHP-League/CSV
        $this->insertIntoCsvForSequential($this->csv2->getRecords());
        # On retourne un booléen, true si au moins un client a été ajouté, false si aucun n'a été ajouté
        return $this->clientAjouter === 1;
    }

    /**
     * @return bool
     */
    public function interlaced(): bool
    {
        # Si le nombre de lignes dans le tableau csv1 est supérieur au tableau csv2,
        # la variable $max aura comme valeur, le nombre de lignes du tableau csv1 sinon
        # elle aura comme valeur le nombre de lignes du tableau csv2
        $max = ($this->csv1->count() > $this->csv2->count()) ? $this->csv1->count() : $this->csv2->count();
        $csv1Index = 0;
        $csv2Index = 0;

        # Boucles do-while qui se lancera tant que la variable $max sera supérieur à 0
        do {
            # Variable pour vérifier si un client de fichier csv1 a été ajouté au fichier final
            $customerIsInserted = false;
            # Boucle while qui se lancera tant que la variable $customerIsInserted sera égal à false
            # et que la variable $csv1Index sera inférieur au nombre de données dans le tableau csv1
            while ($customerIsInserted == false && $csv1Index < $this->csv1->count()) {
                # Déclaration du tableau $content
                $content = [];
                # On lit chaque valeur du tableau $column envoyé en paramètre
                foreach ($this->columns as $column) {
                    # On change chaque clé de la ligne x (valeur $csv1Index) du tableau csv1 en minuscule
                    $data = array_change_key_case($this->csv1->fetchOne($csv1Index), CASE_LOWER);
                    # Chaque valeur lut avec la clé "column" du tableau $data est ajouté dans le tableau $content
                    $content[] = $data[$column];
                }
                # On vérifie si le client est adulte avec la fonction estAdulte, s'il a une taille valide avec la fonction aUneTailleValide
                # et si le code de sa carte de crédit est unique avec la fonction getDuplicateValueInArray
                if (Verification::estAdulte($data["birthday"]) && Verification::aUneTailleValide($data["feetinches"], $data["centimeters"]) && !in_array($data["ccnumber"], Csv::getDuplicateValueInArray($this->arrayOfCsvCcNumber))) {
                    # Si le client, réussi à passer tous ses tests, il est ajouté avec la fonction insertOne de la librairie PHP-League/CSV au tableau $content
                    $this->mergeCsv->insertOne($content);
                    # Un client est ajouté, la valeur de la variable $customerIsInserted passe donc en true
                    $customerIsInserted = true;
                    # Un client est ajouté, la valeur de l'attribut clientAjouter passe donc a 1
                    $this->clientAjouter = 1;
                }
                # On post-incrémente la variable $csv1Index
                $csv1Index++;
            }
            # On remet la variable $customerIsInserted à false pour vérifier si un client du fichier csv2 a été ajouté au fichier final
            $customerIsInserted = false;
            # Boucle while qui se lancera tant que la variable $customerIsInserted sera égal à false
            # et que la variable $csv1Index sera inférieur au nombre de données dans le tableau csv2
            while ($customerIsInserted == false && $csv2Index < $this->csv2->count()) {
                # Déclaration du tableau $content
                $content = [];
                # On lit chaque valeur du tableau $column envoyé en paramètre
                foreach ($this->columns as $column) {
                    # On change chaque clé de la ligne x (valeur $csv2Index) du tableau csv2 en minuscule
                    $data = array_change_key_case($this->csv2->fetchOne($csv2Index), CASE_LOWER);
                    # Chaque valeur lut avec la clé "column" du tableau $data est ajouté dans le tableau $content
                    $content[] = $data[$column];
                }
                # On vérifie si le client est adulte avec la fonction estAdulte, s'il a une taille valide avec la fonction aUneTailleValide
                # et si le code de sa carte de crédit est unique avec la fonction getDuplicateValueInArray
                if (Verification::estAdulte($data["birthday"]) &&  Verification::aUneTailleValide($data["feetinches"], $data["centimeters"]) && !in_array($data["ccnumber"], Csv::getDuplicateValueInArray($this->arrayOfCsvCcNumber))) {
                    # Si le client, réussi à passer tous ses tests, il est ajouté avec la fonction insertOne de la librairie PHP-League/CSV au tableau $content
                    $this->mergeCsv->insertOne($content);
                    # Un client est ajouté, la valeur de la variable $customerIsInserted passe donc en true
                    $customerIsInserted = true;
                    # Un client est ajouté, la valeur de l'attribut clientAjouter passe donc a 1
                    $this->clientAjouter = 1;
                }
                # On post-incrémente la variable $csv1Index
                $csv2Index++;
            }

            # On post-décrémente la variable $max
            $max--;
        } while ($max > 0);
        # On retourne un booléen, true si au moins un client a été ajouté, false si aucun n'a été ajouté
        return $this->clientAjouter === 1;
    }


    public function downloadCsv($nomCsv = "french-german-client-"): int
    {
        # On retourne les valeurs de l'attribut mergeCsv avec la fonction output de la librairie
        # PHP-League/Csv en lui donner comme paramètre (comme nom), la valeur de la variable $nomCsv
        # + la date d'aujourd'hui en format (Y-m-d) avec la fonction date puis .csv afin de lui donner
        # le format csv au téléchargement
        return $this->mergeCsv->output($nomCsv . date('Y-m-d') . '.csv');
    }

    /**
     * @return array
     */
    public function getColumns(): array
    {
        # On récupère les valeurs de l'attribut colums qui est un tableau
        return $this->columns;
    }

    /**
     * @return AbstractCsv|Reader
     */
    public function getCsv1()
    {
        # Avec la fonction AbstractCsv nous pouvons lire le fichier csv nommé csv1
        return $this->csv1;
    }

    /**
     * @return AbstractCsv|Reader
     */
    public function getCsv2()
    {
        # Avec la fonction AbstractCsv nous pouvons lire le fichier csv nommé csv2
        return $this->csv2;
    }


}