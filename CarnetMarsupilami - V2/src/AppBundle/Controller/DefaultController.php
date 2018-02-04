<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/user/test", name="testRoleUser")
     */
     public function testRoleUserAction(Request $request){
       return $this->render('Exemples_Roles/hello-world.html.twig');
     }

     /**
      * @Route("/admin/test", name="testRoleAdmin")
      */
      public function testRoleAdminAction(Request $request){
        return $this->render('Exemples_Roles/hello-world-admin.html.twig');
      }

      /**
       * @Route("/user/info", name="infoUser")
       */
       public function infoUserAction(Request $request){
         $amis= $this->getUser()->getfriends();
         if(isset($_POST["name"]) && isset($_POST["mail"]) && isset($_POST["content"]) ){
           if (trim($_POST["name"])!="" && trim($_POST["mail"])!="" && trim($_POST["content"])!="") {
             /*var_dump($_POST["name"]);
             var_dump($_POST["mail"]);
             var_dump($_POST["content"]);*/
             $friendN= $_POST["name"];
             $friendM= $_POST["mail"];
             $friendC= $_POST["content"];
             unset($_POST);
             echo '<p style="color:green;">Invitation envoyée :)</p>';
             return $this->render('mail.html.twig',array('friendN' =>$friendN ,'friendM' =>$friendM ,'friendC' =>$friendC) );
           }
          }
         return $this->render('information-utilisateur.html.twig',array('amis' =>$amis , ));
       }

        /**
         * @Route("/user/all-user", name="AllUser")
         */
         public function AllUserAction(Request $request)
         {
           $em = $this->getDoctrine()->getManager();

           $users = $em->getRepository('AppBundle:User')->findAll();

           $users_lite= array();
           $i=0;
          $amis= $this->getUser()->getFriends();
           foreach ($users as $value) {
             $relation=false;
             foreach ($amis as  $ami) {
               $idAmi=$ami->getId();
               $idUser=$value->getId();
               if ($idAmi == $idUser) {
                 $relation=true;
               }
              }
              if ($relation) {
                $users_lite[$i]=
                    ['id'=>$value->getId(),
                    'username'=>$value->getUsername(),
                    'age'=>$value->getAge(),
                    'famille'=>$value->getFamille(),
                    'race'=>$value->getRace(),
                    'nourriture'=>$value->getNourriture(),
                    'relation'=>"vous êtes déjà ami avec ce marsupalami"
                  ];
              }else {
                $users_lite[$i]=
                    ['id'=>$value->getId(),
                    'username'=>$value->getUsername(),
                    'age'=>$value->getAge(),
                    'famille'=>$value->getFamille(),
                    'race'=>$value->getRace(),
                    'nourriture'=>$value->getNourriture(),
                    'relation'=>"vous n'êtes pas encore ami ce marsupalami"
                  ];
              }

              $i++;
           }
           $contenuJson= json_encode($users_lite);
           //var_dump($contenuJson);
           $nomFichier='../src/AppBundle/Resources/public/js/all.json';
           $fichier= fopen($nomFichier,'w+');
           fwrite($fichier,$contenuJson);
           fclose($fichier);

           return $this->render('voir-utilisateurs.html.twig', array(
               'users' => $users,
           ));

         }



         /**
          * @Route("/user/{id}/informations", name="infoOtherUser")
          */
          public function infoOtherUserAction(Request $request,User $user)
          {
            $dejaAmi=false;
            $idAmiAct= $user->getId();
            $listF= $this->getUser()->getFriends();
            foreach ($listF as $value) {
              $idLiset= $value->getId();
              if ($idLiset == $idAmiAct) {
                $dejaAmi=true;
              }
            }

            if ($dejaAmi) {
              $etat="vous n'êtes pas amis avec ce marsupalami";
              $listUser = $this->getUser()->removeFriend($user);
            }else {
              $etat="vous êtes amis avec ce marsupalami";
              $listUser = $this->getUser()->addFriend($user);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($listUser);
            $em->flush();
            return $this->render('voir-infos-utilisateur.html.twig', array(
                'user' => $user,
                'etat' => $etat,
            ));

          }


           /**
            * @Route("/user/modif-info", name="edit")
            */
          public function editAction(Request $request)
          {
              $user = $this->getUser();
              $editForm = $this->createForm('AppBundle\Form\UserType', $user);
              $editForm->handleRequest($request);

              if ($editForm->isSubmitted() && $editForm->isValid()) {
                  $this->getDoctrine()->getManager()->flush();
                  $amis= $this->getUser()->getfriends();
                  return $this->render('information-utilisateur.html.twig', array(
                      'user' => $user,
                      'amis' => $user,
                  ));              }

              return $this->render('information-modification.html.twig', array(
                  'user' => $user,
                  'edit_form' => $editForm->createView(),
              ));
          }


          public function deleteAction(Request $request, User $user)
          {
              $form = $this->createDeleteForm($user);
              $form->handleRequest($request);

              if ($form->isSubmitted() && $form->isValid()) {
                  $em = $this->getDoctrine()->getManager();
                  $em->remove($user);
                  $em->flush();
              }

              return $this->redirectToRoute('/');
          }

          /**
           * Creates a form to delete a user entity.
           *
           * @param User $user The user entity
           *
           * @return \Symfony\Component\Form\Form The form
           */
          private function createDeleteForm(User $user)
          {
              return $this->createFormBuilder()
                  ->setAction($this->generateUrl('user_delete', array('id' => $user->getId())))
                  ->setMethod('DELETE')
                  ->getForm()
              ;
          }





}
