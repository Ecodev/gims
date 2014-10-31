<?php

namespace Application\Controller;

use Zend\Mail;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Questionnaire;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class EmailController extends AbstractActionController
{

    use \Application\Traits\EntityManagerAware;

    /**
     *  Receive a questionnaire and a user role. Returne only users that have that role.
     */
    public function getUsersByRole($questionnaire, $wantedRole)
    {
        $users = $this->getEntityManager()->getRepository('Application\Model\UserQuestionnaire')->getAllWithPermission('read', null, 'questionnaire', $questionnaire);

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

        $users = $this->getUsersByRole($questionnaire, 'Survey editor');

        $subject = 'GIMS - Questionnaire opened : ' . $questionnaire->getName();
        $mailParams = array(
            'questionnaire' => $questionnaire,
        );
        $this->sendMail($users, $subject, new ViewModel($mailParams));
    }

    /**
     * Notify questionnaire creator if (sent by validators)
     */
    public function notifyQuestionnaireCreatorAction()
    {
        $questionnaireId = $this->getRequest()->getParam('id');
        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($questionnaireId);

        //$users = array($questionnaire->getCreator()); // swap with next line to change between selecting the questionnaire creator and the users that have editor role
        $users = $this->getUsersByRole($questionnaire, 'Survey editor');

        $subject = 'GIMS - Questionnaire validated: ' . $questionnaire->getName();
        $mailParams = array(
            'questionnaire' => $questionnaire,
        );
        $this->sendMail($users, $subject, new ViewModel($mailParams));
    }

    /**
     * Notify all questionnaire validators (dispatched by reporters)
     */
    public function notifyQuestionnaireValidatorAction()
    {
        $questionnaireId = $this->getRequest()->getParam('id');
        $questionnaire = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($questionnaireId);

        $users = $this->getUsersByRole($questionnaire, 'Questionnaire validator');

        // The below lines replace the above line (and the method getUsersByRole of this class).
        // They replace the role based feature by a permissions based feature. Instead of notifying Validators, notify everybody that can Validate.
        // getAllHavingPermission() returns a query exception -> why do you do that to us god ?!?
        //
        // $userRepository = $this->getEntityManager()->getRepository('Application\Model\User');
        // $users = $userRepository->getAllHavingPermission($questionnaire, \Application\Model\Permission::getPermissionName($questionnaire, 'validate'));

        $subject = 'GIMS - Questionnaire completed : ' . $questionnaire->getName();
        $mailParams = array(
            'questionnaire' => $questionnaire,
        );

        $this->sendMail($users, $subject, new ViewModel($mailParams));
    }

    /**
     * Send an activation link to specified user, so he can confirm his email is valid
     */
    public function activationLinkAction()
    {
        $userId = $this->getRequest()->getParam('id');
        $user = $this->getEntityManager()->getRepository('Application\Model\User')->findOneById($userId);
        if (!$user) {
            return;
        }

        $user->generateActivationToken();
        $this->getEntityManager()->flush();

        $subject = 'GIMS - Account activation';
        $mailParams = array(
            'token' => $user->getActivationToken(),
        );

        $this->sendMail($user, $subject, new ViewModel($mailParams));
    }

    private function sendMail($users, $subject, ViewModel $model)
    {
        static $emailCount = 0;

        if (!is_array($users)) {
            $users = [$users];
        }

        $config = $this->getServiceLocator()->get('Config');

        foreach ($users as $user) {

            $renderer = $this->getServiceLocator()->get('ViewRenderer');

            // First render the view
            $template = 'application/email/' . $this->getRequest()->getParam('action');
            $model->setTemplate($template);
            $model->setVariable('user', $user);
            $model->setVariable('domain', $this->getServiceLocator()->get('Config')['domain']);
            $partialContent = $renderer->render($model);

            // Then inject it into layout
            $modelLayout = new ViewModel(array($model->captureTo() => $partialContent));
            $modelLayout->setTemplate('application/email/email');
            $modelLayout->setVariable('user', $user);
            $modelLayout->setVariable('domain', $this->getServiceLocator()->get('Config')['domain']);
            $content = $renderer->render($modelLayout);

            // Setup SMTP transport using LOGIN authentication
            $transport = new SmtpTransport();
            $options = new SmtpOptions($config['smtp']);
            $transport->setOptions($options);

            // set Mime type html
            $htmlPart = new MimePart($content);
            $htmlPart->type = "text/html";
            $body = new MimeMessage();
            $body->setParts(array($htmlPart));

            $mail = new Mail\Message();
            $mail->setSubject($subject);
            $mail->setBody($body);
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
            file_put_contents($log, $mail->getBodyText());

            echo 'email sent to: ' . $user->getName() . "\t" . $email . "\t" . $subject . PHP_EOL;
        }
    }

}
