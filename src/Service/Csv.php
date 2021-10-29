<?php

# Nom du package
namespace App\Service;

# Importation des classes nécessaires
use League\Csv\Reader;

# Nom de la classe
class Csv
{
    /**
     * @return array avec seulement les colonnes spécifiques
     */
    public static function getSpecificColumns(): array
    {
        return ['Gender', 'Title', 'Surname', 'GivenName', 'EmailAddress', 'Birthday', 'TelephoneNumber', 'CCType', 'CCNumber', 'CVV2', 'CCExpires', 'StreetAddress', 'City', 'ZipCode', 'CountryFull', 'Centimeters', 'Kilograms', 'Vehicle', 'Latitude', 'Longitude'];
    }

    /**
     * @param $csvDescriptor
     * @return array avec uniquement l'entête d'un fichier csv
     */
    public static function getCsvHeader($csvDescriptor): array
    {
        # On récupère grace a la fonction setHeaderOffset de la librairie PHP League/CSV la première ligne d'un fichier Csv
        return Reader::createFromPath($csvDescriptor)->setHeaderOffset(0)->getHeader();
    }

    /**
     * @param $uploadedCsvHeader
     * @return bool Vrai (true) si le header du Csv est valide sinon Faux (false)
     */
    public static function isValidCsvHeader($uploadedCsvHeader): bool
    {
        # Déclaration de l'attribut $uploadedCsvHeader avec comme argument la valeur de retour de array_map avec comme paramètre $uploadedCsvHeader
        $uploadedCsvHeader = array_map('strtolower', $uploadedCsvHeader);
        $i = 0;
        # Déclaration de l'attribut $csvColums avec comme argument la valeur de retour de array_map avec comme paramètre le retour de
        # la fonction getSpecificColums de la classe Csv
        $csvColumns = array_map('strtolower', Csv::getSpecificColumns());
        # On lit chaque valeur du tableau $uploadedCsvHeader
        foreach ($uploadedCsvHeader as $value) {
            # Si la valeur lut correspond à la valeur située dans le tableau $csvColums on ajoute +1 à la variable $i
            if (in_array($value, $csvColumns)) {
                $i++;
            }
        }
        # On retourne True si $i est égal à la valeur retournée de count $csvColums sinon False
        return $i === count($csvColumns);
    }

    /**
     * @param $csv1Data
     * @param $csv2Data
     * @return array
     */
    public static function getArrayOfCsvCcNumber2Csv($csv1Data, $csv2Data): array
    {
        # Déclaration du tableau $arrayOfCcNumber
        $arrayOfCcNumber = [];
        # On lit chaque valeur du tableau $csv1Data envoyé en paramètre
        foreach ($csv1Data as $data) {
            $data = array_change_key_case($data, CASE_LOWER);
            # Chaque valeur lut avec la clé "ccnumber" est ajouté dans le tableau $arrayOfCcNumber
            $arrayOfCcNumber[] = $data["ccnumber"];
        }
        # On lit chaque valeur du tableau $csv1Data envoyé en paramètre
        foreach ($csv2Data as $data) {
            $data = array_change_key_case($data, CASE_LOWER);
            # Chaque valeur lut avec la clé "ccnumber" est ajouté dans le tableau $arrayOfCcNumber
            $arrayOfCcNumber[] = $data["ccnumber"];
        }
        return $arrayOfCcNumber;
    }

    /**
     * @param $csvData
     * @return array
     */
    # Cette fonction à le meme fonctionnement que la fonction getArrayOfCsvCcNumber mais avec qu'un seul paramètre envoyé
    public static function getArrayOfCsvCcNumber($csvData): array
    {
        $arrayOfCcNumber = [];
        foreach ($csvData as $data) {
            $data = array_change_key_case($data, CASE_LOWER);
            $arrayOfCcNumber[] = $data["ccnumber"];
        }
        return $arrayOfCcNumber;
    }

    /**
     * @param $array
     * @return array $rValue
     */
    public static function getDuplicateValueInArray($array): array
    {
        # Déclaration d'un tableau vide stocker dans la variable $arrayOfCcNumber
        $rValue = [];
        # Déclaration d'un tableau array_unique avec comme paramètre les valeurs du tableau $array
        $arrayUnique = array_unique($array);

        if (count($array) - count($arrayUnique)) {
            for ($i = 0; $i < count($array); $i++) {
                if (!array_key_exists($i, $arrayUnique)) {
                    $rValue[] = $array[$i];
                }
            }
        }

        return array_unique($rValue);
    }

}