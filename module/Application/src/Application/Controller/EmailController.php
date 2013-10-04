<?php

namespace Application\Controller;


use Zend\Mail;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use Application\Model\Questionnaire;

class EmailController extends AbstractActionController
{

    use \Application\Traits\EntityManagerAware;


    /**
     *  Receive a questionnaire and a user role. Returne only users that have that role.
     */
    public function getUsersByRole($questionnaire, $wantedRole)
    {
        $users = $this->getEntityManager()->getRepository('Application\Model\UserQuestionnaire')->getAllWithPermission('validator', 'questionnaire', $questionnaire);

        $selectedUsers = array();
        foreach($users as $user){
            if ($user->getRole()->getName() == $wantedRole) {
                array_push($selectedUsers, $user->getUser());
            }
        }
        return $selectedUsers;
    }



    /**
     * Notify questionnaire reporters if the questionnaire is again editable
     */
    public function notifyQuestionnaireReportersAction()
    {
        $questionnaireId = $this->getRequest()->getParam('id');
        $questionnaire = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($questionnaireId);

        $users = $this->getUsersByRole($questionnaire, 'validator');
        $this->sendMail($users, 'The questionnaire '.$questionnaire->getName().' has been re-opened.');

    }


    /**
     * Notify questionnaire creator if (sent by validators)
     */
    public function notifyQuestionnaireCreatorAction()
    {
        echo 'notif creator email';
        $questionnaireId = $this->getRequest()->getParam('id');
        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($questionnaireId);

        $creator = $questionnaire->getCreator();
        $this->sendMail(array($creator), 'The questionnaire '.$questionnaire->getName().' has been validated.') ;
    }




    /**
     * Notify all questionnaire validators (dispatched by reporters)
     */
    public function notifyQuestionnaireValidatorAction()
    {
        $questionnaireId = $this->getRequest()->getParam('id');
        $questionnaire = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($questionnaireId);

        $users = $this->getUsersByRole($questionnaire, 'validator');

//        $renderer = new PhpRenderer();
//        $resolver = new Resolver\AggregateResolver();
//        $renderer->setResolver($resolver);
//        $model = new ViewModel(array('test' => 'test ok'));
//        $model->setTemplate('email/email');
//        $content = $renderer->render($model);

        $this->sendMail($users, 'The questionnaire '.$questionnaire->getName().' has been completed.');

    }




    public function sendMail($users, $subject, $content=null)
    {
        if (count($users) > 0) {

            $config = $this->getServiceLocator()->get('Config');

            // Setup SMTP transport using LOGIN authentication
            $transport = new SmtpTransport();
            $options   = new SmtpOptions( $config['smtp']);
            $transport->setOptions($options);

            $mail = new Mail\Message();
            $mail->setSubject($subject);
            $mail->setBody($subject);
            $mail->setFrom('webmaster@gimsinitiative.org', 'Gims project');

            if (isset($config['emailOverride']) && !empty($config['emailOverride'])) {
                $mail->addTo($config['emailOverride']);
            } else {
                foreach ($users as $user) {
                    $mail->addTo($user->getEmail(), $user->getDisplayName());
                }
            }

            $dateNow = new \DateTime();
            $dateNow = $dateNow->format('Y-m-d_H:i:s,u');

            $log = 'data/logs/emails/' . $dateNow .'.html';
            file_put_contents($log, $mail->toString());

            $transport->send($mail);
            return $mail->toString()."\n";
        }
    }

}