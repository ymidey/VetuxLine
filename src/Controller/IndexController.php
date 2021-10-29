<?php

# Nom du package
namespace App\Controller;

# Importation des classes nécessaires
use App\Form\SearchMarkForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Customer;
use App\Entity\Mark;
use App\Entity\Vehicle;
use App\Form\InvalidCsvForm;
use App\Form\MergeCsvForm;
use App\Form\EtlCsvForm;
use App\Repository\CustomerRepository;
use App\Repository\MarkRepository;
use App\Service\Csv;
use App\Service\Etl;
use App\Service\Merge;
use App\Service\Invalid;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;

/**
 * @Route("/admin/", name="admin_")
 * @IsGranted("ROLE_ADMIN")
 */
class IndexController extends AbstractController
{
    # Déclaration des attributs
    private $entityManager;

    private $customerRepository;

    private $markRepository;

    public function __construct(EntityManagerInterface $entityManager, CustomerRepository $customerRepository, MarkRepository $markRepository)
    {
        # Ajout des arguments aux différents attributs
        $this->entityManager = $entityManager;
        $this->customerRepository = $customerRepository;
        $this->markRepository = $markRepository;
    }

    /**
     * @Route("fusion", name="merge")
     */
    public function merge(Request $request)
    {
        # On appelle formulaire situé dans la classe MergeCsvForm
        $csvMergeForm = $this->createForm(MergeCsvForm::class);
        $csvMergeForm->handleRequest($request);

        # On vérifie que le formulaire a été renvoyé et qu'il est valide (fichier Csv uniquement)
        if ($csvMergeForm->isSubmitted() && $csvMergeForm->isValid()) {

            # On récupère dans la variable $csv1Data les données envoyées dans la valeur post csv1
            $csv1Data = $csvMergeForm['csv1']->getData();
            # On récupère dans la variable $csv2Data les données envoyées dans la valeur post csv2
            $csv2Data = $csvMergeForm['csv2']->getData();

            # On vérifie avec la variable isValidCsvHeader de la classe Csv, si l'en-tête des données de la variable $csv1data
            # n'est pas égal à l'en-tête du retour de la fonction getSpecificColumn situé dans la classe Csv
            if (!Csv::isValidCsvHeader(Csv::getCsvHeader($csv1Data))) {
                # Si l'en-tête de $csv1Data n'est bien pas égal à l'en-tete de getSpecificColumn, on renvoie
                # un message flash à l'utilisateur
                $this->addFlash("error", "Le fichier Csv 1 ne contient pas toutes les colonnes nécessaire à la fusion");
                # Puis, on le redirige à la route 'admin_merge'
                $this->redirectToRoute("admin_merge");
            }

            # On vérifie avec la variable isValidCsvHeader de la classe Csv, si l'en-tête des données de la variable $csv2Data
            # n'est pas égal à l'en-tête du retour de la fonction getSpecificColumn situé dans la classe Csv
            if (!Csv::isValidCsvHeader(Csv::getCsvHeader($csv2Data))) {
                # Si l'en-tête de $csv1Data n'est bien pas égal à l'en-tete de getSpecificColumn, on renvoie
                # un message flash de type erreur à l'utilisateur
                $this->addFlash("error", "Le fichier Csv 2 ne contient pas toutes les colonnes nécessaire à la fusion");
                # Puis, on le redirige à la route 'admin_merge'
                $this->redirectToRoute("admin_merge");
            }

            # On appelle le constructeur Merge (App\Service\Merge) en lui donnant comme paramètre $csv1Data et $csv2Data
            $merge = new Merge($csv1Data, $csv2Data);

            # On vérifie si la valeur envoyé en post nommé type est égal à "Séquentiel"
            if ($csvMergeForm["type"]->getData() == "Séquentiel") {
                # Si c'est le cas, dans la variable $fusionSequenciel, on appelle la fonction sequential située dans la classe Service\Merge avec comme paramètre $merge
                $fusionSequenciel = $merge->sequential();
                # On vérifie que la variable $fusionSequenciel n'est pas vide
                if ($fusionSequenciel) {
                    # Si, elle ne l'est pas, on appelle la fonction download de la classe Merge
                    # qui aura pour but de télécharger les données situé dans ma variable $merge
                    # en fichier Csv
                    $merge->downloadCsv();
                } else {
                    # Si, la variable $fusionSequenciel est vide, ce qui veut dire qu'aucun client a été ajouté au fichier final
                    # on renvoie un message flash de type erreur à l'utilisateur
                    $this->addFlash("error", "La fusion des fichiers n'a pas abouti, toutes les données des clients étaient vides ou contenaient des données invalides");
                    # Puis, on le redirige à la route 'admin_merge'
                    $this->redirectToRoute("admin_merge");
                }
            }

            # On vérifie si la valeur envoyé en post nommé type est égal à "Séquentiel"
            if ($csvMergeForm["type"]->getData() == "Entrelacé") {
                # Si c'est le cas, dans la variable $fusionEntrelace, on appelle la fonction interlaced situé dans la classe Service\Merge avec comme paramètre $merge
                $fusionEntrelace = $merge->interlaced();
                # On vérifie que la variable $fusionSequenciel n'est pas vide
                if ($fusionEntrelace) {
                    # Si, elle ne l'est pas, on appelle la fonction download de la classe Merge
                    # qui aura pour but de télécharger les données situé dans ma variable $merge
                    # en fichier Csv
                    $merge->downloadCsv();
                } else {
                    # Si, la variable $fusionSequenciel est vide, ce qui veut dire qu'aucun client a été ajouté au fichier final
                    # on renvoie un message flash de type erreur à l'utilisateur
                    $this->addFlash("error", "La fusion des fichiers n'a pas abouti, toutes les données des clients étaient vides ou contenaient des données invalides");
                    # Puis, on le redirige à la route 'admin_merge'
                    $this->redirectToRoute("admin_merge");
                }
            }

            return new Response();
        }

        # On envoie le formulaire à l'utilisateur
        return $this->render('admin/fusion.html.twig', [
            "mergeCsvForm" => $csvMergeForm->createView()]);
    }

    /**
     * @Route("invalide/client", name="invalid_customers")
     */
    public function invalidCustomers(Request $request)
    {
        # On appelle formulaire situé dans la classe InvalidCsvForm
        $csvForm = $this->createForm(InvalidCsvForm::class);
        $csvForm->handleRequest($request);

        # On vérifie que le formulaire a été renvoyé et qu'il est valide (fichier Csv uniquement)
        if ($csvForm->isSubmitted() && $csvForm->isValid()) {
            # On récupère dans la variable $type les données envoyées dans la valeur post type
            $type = $csvForm['type']->getData();
            # On récupère dans la variable $csvData les données envoyées dans la valeur post csv
            $csvData = $csvForm['csv']->getData();

            # On vérifie avec la variable isValidCsvHeader de la classe Csv, si l'en-tête des données de la variable $csvData
            # n'est pas égal à l'en-tête du retour de la fonction getSpecificColumn situé dans la classe Csv
            if (!Csv::isValidCsvHeader(Reader::createFromPath($csvData)->setHeaderOffset(0)->getHeader())) {
                # Si l'en-tête de $csv1Data n'est bien pas égal à l'en-tete de getSpecificColumn, on renvoie
                # un message flash à l'utilisateur
                $this->addFlash("error", "Le fichier Csv ne contient pas les colonnes nécessaire à la fusion");
                # Puis, on le redirige à la route 'admin_merge'
                $this->redirectToRoute("admin_invalid_customers");
            }

            # On appelle le constructeur Invalid (App\Service\Invalid) en lui donnant comme paramètre $csvData et $type
            $invalidMerge = new Invalid($csvData, $type);

            # Dans la variable $fusionInvalide, on appelle la fonction fusion située dans la classe Service\Invalid
            $fusionInvalide = $invalidMerge->fusion();
            # On vérifie que la variable $fusionInvalide n'est pas vide
            if ($fusionInvalide) {
                # Si, elle ne l'est pas, on appelle la fonction download de la classe Merge
                # qui aura pour but de télécharger les données situé dans ma variable $merge
                # en fichier Csv
                $invalidMerge->downloadCsv();
            } else {
                # Si, la variable $fusionInvalide est vide, ce qui veut dire qu'aucun client a été ajouté au fichier final
                # on renvoie un message flash de type erreur à l'utilisateur
                $this->addFlash("error", "La fusion des fichiers n'a pas abouti, toutes les données des clients étaient vides ou contenaient uniquement des données valides ou des données invalides d'un autre type");
                # Puis, on le redirige à la route 'admin_invalid_customers'
                $this->redirectToRoute("admin_invalid_customers");
            }

            return new Response();
        }
        # On envoie le formulaire à l'utilisateur
        return $this->render('admin/client_invalide.html.twig', ["csvForm" => $csvForm->createView()]);
    }

    /**
     * @Route("ajout/client", name="add_customers")
     */
    public function addCustomers(Request $request)
    {
        # On appelle formulaire situé dans la classe EtlCsvForm
        $csvForm = $this->createForm(EtlCsvForm::class);
        $csvForm->handleRequest($request);

        # On vérifie que le formulaire a été renvoyé et qu'il est valide (fichier Csv uniquement)
        if ($csvForm->isSubmitted() && $csvForm->isValid()) {
            # On ajoute à la variable $csv l'en-tête des données reçu dans le paramètre csv du formulaire
            # grâce à la fonction Reader ainsi que setHeaderOffset de la librairie PHP-League/Csv
            $csv = Reader::createFromPath($csvForm["csv"]->getData())->setHeaderOffset(0);

            # La variable $etl va recevoir le retour de la fonction etl (un tableau) situé dans la classe Etl
            # avec comme paramètre la variable $csv, l'attribut entityManager ainsi que l'attribut markRepository
            $etl = Etl::etl($csv, $this->entityManager, $this->customerRepository, $this->markRepository);

            # Si la clé isValidColums du tableau $etl est strictement égal à 0
            if ($etl["isValidColumns"] === 0) {
                # On renvoie un message flash de type erreur à l'utilisateur
                $this->addFlash("error", "Le fichier csv ne contient pas toutes les colonnes nécessaire à l'insertion dans la base de données");
            }
            # Si la clé customerExist du tableau $etl est strictement égal à 0
            if ($etl["customerExist"] === 0) {
                # On renvoie un message flash de type erreur à l'utilisateur
                $this->addFlash("error", "Un ou plusieurs clients ont déjà été ajouter dans la base de données");
            }
            # Si la clé isValidColums du tableau $etl est strictement égal à 0
            if ($etl["isMajor"] === 0) {
                # On renvoie un message flash de type erreur à l'utilisateur
                $this->addFlash("error", "Certains clients n'avaient pas l'âge légal (18 ans), ils ont donc pas pu être insérés dans la base de données");
            }
            # Si la clé isValidColums du tableau $etl est strictement égal à 0
            if ($etl["isValidSize"] === 0) {
                # On renvoie un message flash de type erreur à l'utilisateur
                $this->addFlash("error", "Certains clients avaient une taille en centimètre et une taille en inch différentes, ils ont donc pas pu être insérés dans la base de données");
            }
            # Si la clé isValidColums du tableau $etl est strictement égal à 0
            if ($etl["isValidCcNumber"] === 0) {
                # On renvoie un message flash de type erreur à l'utilisateur
                $this->addFlash("error", "Certain client avait un code de carte crédit identique à un autre client, ils ont donc pas pu être insérés dans la base de données");
            }
            # Si la clé isValidColums du tableau $etl est strictement égal à 0
            if ($etl["added"] === 0) {
                # On renvoie un message flash de type erreur à l'utilisateur
                $this->addFlash("error", "Tout les clients données dans le fichier était déjà ajouté dans la base de données ou contenait des données invalides");
            }
            # Si la clé added du tableau $etl est strictement égal à 1
            if ($etl["added"] === 1) {
                # On execute la requète flush de la librairie doctrine avec les
                # valeurs de l'attribut entityManager
                $this->entityManager->flush();
                # On renvoie un message flash de type success à l'utilisateur
                $this->addFlash("success", "Les clients avec des données valides ont bien été ajouté à la base de données");
                # On redirige l'utilisateur sur la route 'admin_show_customers'
                return $this->redirectToRoute("admin_show_customers");
            }
            # On redirige l'utilisateur sur la route 'admin_add_customers'
            return $this->redirectToRoute("admin_add_customers");
        }

        # On envoie le formulaire à l'utilisateur
        return $this->render('admin/ajout_client.html.twig', [
            "customers" => $this->customerRepository->findAll(),
            "csvForm" => $csvForm->createView()
        ]);
    }

    /**
     * @Route("tableau/client", name="show_customers")
     */
    public function showCustomers(Request $request)
    {
        $customers = $this->customerRepository->findAll();

        return $this->render('admin/tableau_client.html.twig', [
            "headers" => Csv::getSpecificColumns(),
            "customers" => $customers,
        ]);
    }

    /**
     * @Route("reset", name="reset")
     */
    public function deleteCustomers()
    {
        # On vérifie avec la fonction isGranted de la librairie security du frameWork Symfony
        # si le compte connecté à bien le role admin
        if ($this->isGranted('ROLE_ADMIN')) {
            # On donne à la variable $entityManager la valeur de retour de la méthode $this->getDoctrine->getManager
            $entityManager = $this->getDoctrine()->getManager();
            # On donne à la variable $customers la valeur de retour de la méthode $entityManager->getRepository(Customer::class)->findAll
            $customers = $entityManager->getRepository(Customer::class)->findAll();
            # On donne à la variable $mark la valeur de retour de la méthode $entityManager->getRepository(Mark::class)->findAll
            $mark = $entityManager->getRepository(Mark::class)->findAll();
            # On donne à la variable $vehicle la valeur de retour de la méthode $entityManager->getRepository(Vehicle::class)->findAll
            $vehicle = $entityManager->getRepository(Vehicle::class)->findAll();
            # On lit chaque valeur du tableau $customers
            foreach ($customers as $value) {
                # On notifie doctrine avec la méthode remove en lui disant que l'on voudrait supprimer chaque valeur donnée par la variable $value
                $entityManager->remove($value);
                # Avec la méthode flush de doctrine, on supprime chaque valeur donnée par la variable $value
                $entityManager->flush($value);
            }
            # On lit chaque valeur du tableau $customers
            foreach ($vehicle as $value) {
                # On notifie doctrine avec la méthode remove en lui disant que l'on voudrait supprimer chaque valeur donnée par la variable $value
                $entityManager->remove($value);
                # Avec la méthode flush de doctrine, on supprime chaque valeur donnée par la variable $value
                $entityManager->flush($value);
            }
            # On lit chaque valeur du tableau $customers
            foreach ($mark as $value) {
                # On notifie doctrine avec la méthode remove en lui disant que l'on voudrait supprimer chaque valeur donnée par la variable $value
                $entityManager->remove($value);
                # Avec la méthode flush de doctrine, on met à jour notre base de données
                $entityManager->flush($value);
            }
            # On renvoie un message flash de type success à l'utilisateur
            $this->addFlash('success', 'Votre base de données à bien été mise à zéro !');

            # On redirige l'utilisateur sur la route 'admin_show_customers'
            return $this->redirectToRoute("admin_show_customers");
        }
        # Si, le compte connecté, n'a pas le role admin
        else {
            # On redirige l'utilisateur sur la route 'security_login'
            return $this->redirectToRoute('security_login');
        }
    }

}
