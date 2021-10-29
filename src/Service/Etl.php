<?php

# Nom du package
namespace App\Service;

# Importation des classes nécessaires
use App\Entity\Customer;
use App\Entity\Mark;
use App\Entity\Vehicle;

# Nom de la classe
class Etl
{

    /**
     * @param $entityManager
     * @param $data
     */
    private function addCustomers($entityManager, $data, $markRepository){
        # Déclaration des arguments (valeur) pour l"entité Customer avec comme valeur les données envoyé par $data
        $customer = new Customer();
        $customer->setGender($data["gender"]);
        $customer->setTitle($data["title"]);
        $customer->setSurname($data["surname"]);
        $customer->setGivenName($data["givenname"]);
        $customer->setEmailAddress($data["emailaddress"]);
        $customer->setBirthday($data["birthday"]);
        $customer->setTelephoneNumber($data["telephonenumber"]);
        $customer->setCCType($data["cctype"]);
        $customer->setCcNumber($data["ccnumber"]);
        $customer->setCvv2($data["cvv2"]);
        $customer->setCCExpires($data["ccexpires"]);
        $customer->setStreetAddress($data["streetaddress"]);
        $customer->setCity($data["city"]);
        $customer->setZipCode($data["zipcode"]);
        $customer->setCountryFull($data["countryfull"]);
        $customer->setCentimeters($data["centimeters"]);
        $customer->setKilograms($data["kilograms"]);
        $explodeVehicle = \explode(" ", $data["vehicle"]);
        # Déclaration des arguments (valeur) pour l"entité Vehicle avec comme valeur les données envoyé par $data
        $vehicle = new Vehicle();
        $vehicle->setYear($explodeVehicle[0]);
        $vehicle->setModel($explodeVehicle[2]);
        # On vérifie si la marque de la voiture existe
        if ($markRepository->findOneBy(["name" => $explodeVehicle[1]])){
            $vehicleMark = $markRepository->findOneBy(["name" => $explodeVehicle[1]]);
        }else{
            # Si elle n'existe pas, on la crée
            $vehicleMark = new Mark();
            $vehicleMark->setName($explodeVehicle[1]);
        }
        $vehicleMark->addVehicle($vehicle);
        $customer->setVehicle($vehicle);
        $vehicle->setMark($vehicleMark);
        $vehicle->addCustomer($customer);
        $customer->setLatitude($data["latitude"]);
        $customer->setLongitude($data["longitude"]);
        $entityManager->persist($customer);
    }

    /**
     * @param $csv
     * @param $entityManager
     * @param $customerRepository
     * @return int[]
     */
    public static function etl($csv, $entityManager, $customerRepository, $markRepository){
        # Déclaration des attributs
        $isValidColumns = 1;
        $customerExist = 1;
        $isMajor = 1;
        $isValidSize = 1;
        $isValidCcNumber = 1;
        $added = 0;

        # On vérifie que l'entête du fichier Csv reçue est bien valide
        if(Csv::isValidCsvHeader($csv->getHeader())){
            # On lit tout le fichier csv avec la fonction getRecords provenant de la librairie PHP-League/Csv
            foreach($csv->getRecords() as $data){
                $data = array_change_key_case($data, CASE_LOWER);
                # On vérifie que le client possède un code de carte bleu UNIQUE
                if (!$customerRepository->findOneBy(["ccNumber" => $data["ccnumber"]])) {
                    # On vérifie que le client est majeur
                    if (Verification::estAdulte($data["birthday"])) {
                        # On vérifie que le client possède une donnée 'feetinches'
                        if (array_key_exists("feetinches", $data)) {
                            # Si telle est le cas, on compare sa taille sa taille en feetinches (donnée ["feetinches"]) et en cm (donnée ["centimeters"])
                            if (Verification::aUneTailleValide($data["feetinches"], $data["centimeters"])) {
                                # Si le client passe correctement tous ses tests, il est ajouté
                                (new Etl)->addCustomers($entityManager, $data, $markRepository);
                                $added = 1;
                            } else {
                                $isValidSize = 0;
                            }
                        } else {
                            if (!in_array($data["ccnumber"], Csv::getDuplicateValueInArray(Csv::getArrayOfCsvCcNumber($csv)))) {
                                (new Etl)->addCustomers($entityManager, $data, $markRepository);
                                $added = 1;
                            } else {
                                $isValidCcNumber = 0;
                            }
                        }
                    } else {
                        $isMajor = 0;
                    }
                }else{
                    $customerExist = 0;
                }
            }
        }else{
            $isValidColumns = 0;
        }
        # Retour de variable pour des messages analytics envoyé par message flash au client
        return ["isValidColumns" => $isValidColumns, "customerExist" => $customerExist, "isMajor" => $isMajor, "isValidSize" => $isValidSize, "isValidCcNumber" => $isValidCcNumber, "added" => $added];
    }

}