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
        foreach ($users as $user) {
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
        $this->sendMail($users, 'The questionnaire ' . $questionnaire->getName() . ' has been re-opened.');
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
        $this->sendMail(array($creator), 'The questionnaire ' . $questionnaire->getName() . ' has been validated.');
    }

    /**
     * Notify all questionnaire validators (dispatched by reporters)
     */
    public function notifyQuestionnaireValidatorAction()
    {
        $questionnaireId = $this->getRequest()->getParam('id');
        $questionnaire = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($questionnaireId);

        $users = $this->getUsersByRole($questionnaire, 'validator');

        $subject = 'GIMS - Questionnaire validated: ' . $questionnaire->getName();
        $this->sendMail($users, $subject, new ViewModel(array('questionnaire' => $questionnaire)));
    }

    public function sendMail($users, $subject, ViewModel $model)
    {
        static $emailCount = 0;

        if (count($users) > 0) {

            $config = $this->getServiceLocator()->get('Config');

            foreach ($users as $user) {

                $renderer = $this->getServiceLocator()->get('ViewRenderer');

                // First render the view
                $template = 'application/email/' . $this->getRequest()->getParam('action');
                $model->setTemplate($template);
                $model->setVariable('user', $user);
                $partialContent = $renderer->render($model);

                // Then inject it into layout
                $modelLayout = new ViewModel(array($model->captureTo() => $partialContent));
                $modelLayout->setTemplate('application/email/email');
                $modelLayout->setVariable('user', $user);
                $content = $renderer->render($modelLayout);

                // Setup SMTP transport using LOGIN authentication
                $transport = new SmtpTransport();
                $options = new SmtpOptions($config['smtp']);
                $transport->setOptions($options);

                $mail = new Mail\Message();
                $mail->setSubject($subject);
                $mail->setBody($content);
                $mail->setFrom('webmaster@gimsinitiative.org', 'Gims project');

                $email = $user->getEmail();
                if (isset($config['emailOverride'])) {
                    $email = $config['emailOverride'];
                }

                if ($email) {
                    $mail->addTo($email, $user->getDisplayName());
                    $transport->send($mail);
                }

                $dateNow = new \DateTime();
                $dateNow = $dateNow->format('c');

                $log = 'data/logs/emails/' . $dateNow . '_' . str_pad($emailCount++, 3, '0', STR_PAD_LEFT) . '_' . $email . '.html';
                file_put_contents($log, $mail->getBody());

                echo 'email sent to: ' . $user->getName() . "\t" . $email . "\t" . $subject . "\n";
            }
        }
    }

}
