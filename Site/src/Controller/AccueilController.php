<?php
namespace App\Controller;

use App\Entity\Patient;
use App\Entity\Lit;
use App\Forms\AjoutPatient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class AccueilController
 * @package App\Controller
 *
 * @IsGranted("ROLE_ACCUEIL")
 * @Route("/accueil")
 */
class AccueilController extends AbstractController
{
    //@IsGranted("ROLE_ACCUEIL")à rajouter au dessus

    /**
     * @return Response
     * @Route("/", name="accueil")
     *
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $patients = $em->getRepository('App:Patient')->findBy(array(),array('nompatient' => 'desc'));

        $patient = new Patient();
        $patient->setDateentree(new \DateTime());
        $patient->setEtaturgence('F');

        $lit = $em->getRepository('App:Lit')->findOneBy(['patient' => null]);
        if($lit){
            $patient->setLit($lit);
            $lit->setPatient($patient);

            $form = $this->createForm(AjoutPatient::class, $patient, array(
                'method' => 'POST'
            ));
            $form->add('submit', SubmitType::class, array('label' => 'Ajouter Patient',
                'attr' => array(
                    'class' => "btn btn-contact",
                )));
            if ($request->isMethod('POST'))
            {
                if ($patient->getCivilite()=="0"){
                    $patient->setCivilite('H');
                } else {
                    $patient->setCivilite('F');
                }

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid())
                {
                    $em->persist($patient);
                    $em->flush();

                    $this->addFlash('info', "Le patient a bien été ajouté !");
                    return $this->redirectToRoute('accueil'); // appel ajax to refresh
                }
            }
        } else {
            $this->addFlash('info', "Aucun lit disponible !");
            return $this->render('Accueil/accueil.html.twig', array('patients' => $patients,
                'bool' => false));
        }
        return $this->render('Accueil/accueil.html.twig', array('form' => $form->createView(),
            'patients' => $patients,
            'bool' => true));
    }

}