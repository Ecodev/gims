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
        $users = $this->getEntityManager()->getRepository('Application\Model\UserQuestionnaire')->getAllWithPermission('validator', null, 'questionnaire', $questionnaire);

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

        $users = $this->getUsersByRole($questionnaire, 'editor');

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
        $users = $this->getUsersByRole($questionnaire, 'editor');

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

        $users = $this->getUsersByRole($questionnaire, 'validator');

        // The below lines replace the above line (and the method getUsersByRole of this class).
        // They replace the role based feature by a permissions based feature. Instead of notifying Validators, notify everybody that can Validate.
        // getAllHavingPermission() returns a query exception -> why do you do that to us god ?!?

        // $userRepository = $this->getEntityManager()->getRepository('Application\Model\User');
        // $users = $userRepository->getAllHavingPermission($questionnaire, \Application\Model\Permission::getPermissionName($questionnaire, 'validate'));

        $subject = 'GIMS - Questionnaire completed : ' . $questionnaire->getName();
        $mailParams = array(
            'questionnaire' => $questionnaire,
        );

        $this->sendMail($users, $subject, new ViewModel($mailParams));
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
                file_put_contents($log, $mail->getBody());

                echo 'email sent to: ' . $user->getName() . "\t" . $email . "\t" . $subject . "\n";
            }
        }
    }

}
