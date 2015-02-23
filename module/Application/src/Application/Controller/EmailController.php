<?php

namespace Application\Controller;

use Application\Model\Questionnaire;
use Application\Model\User;
use Application\Utility;
use Zend\Mail;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class EmailController extends AbstractActionController
{

    use \Application\Traits\EntityManagerAware;

    /**
     *  Receive a questionnaire and a user role. Returne only users that have that role.
     */
    public function getUsersByRole($questionnaire, $wantedRole)
    {
        $users = $this->getEntityManager()->getRepository(\Application\Model\UserQuestionnaire::class)->getAllWithPermission('read', null, 'questionnaire', $questionnaire);

        $selectedUsers = [];
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
        $questionnaire = $this->getEntityManager()->getRepository(\Application\Model\Questionnaire::class)->findOneById($questionnaireId);

        $users = $this->getUsersByRole($questionnaire, 'Survey editor');

        $subject = 'Questionnaire opened : ' . $questionnaire->getName();
        $mailParams = [
            'questionnaire' => $questionnaire,
        ];
        $this->sendMail($users, $subject, new ViewModel($mailParams));
    }

    /**
     * Notify questionnaire creator if (sent by validators)
     */
    public function notifyQuestionnaireCreatorAction()
    {
        $questionnaireId = $this->getRequest()->getParam('id');
        /** @var Questionnaire $questionnaire */
        $questionnaire = $this->getEntityManager()->getRepository(\Application\Model\Questionnaire::class)->findOneById($questionnaireId);

        //$users = array($questionnaire->getCreator()); // swap with next line to change between selecting the questionnaire creator and the users that have editor role
        $users = $this->getUsersByRole($questionnaire, 'Survey editor');

        $subject = 'Questionnaire validated: ' . $questionnaire->getName();
        $mailParams = [
            'questionnaire' => $questionnaire,
        ];
        $this->sendMail($users, $subject, new ViewModel($mailParams));
    }

    /**
     * Notify all questionnaire validators (dispatched by reporters)
     */
    public function notifyQuestionnaireValidatorAction()
    {
        $questionnaireId = $this->getRequest()->getParam('id');
        $questionnaire = $this->getEntityManager()->getRepository(\Application\Model\Questionnaire::class)->findOneById($questionnaireId);

        $users = $this->getUsersByRole($questionnaire, 'Questionnaire validator');

        // The below lines replace the above line (and the method getUsersByRole of this class).
        // They replace the role based feature by a permissions based feature. Instead of notifying Validators, notify everybody that can Validate.
        // getAllHavingPermission() returns a query exception -> why do you do that to us god ?!?
        //
        // $userRepository = $this->getEntityManager()->getRepository(\Application\Model\User::class);
        // $users = $userRepository->getAllHavingPermission($questionnaire, \Application\Model\Permission::getPermissionName($questionnaire, 'validate'));

        $subject = 'Questionnaire completed : ' . $questionnaire->getName();
        $mailParams = [
            'questionnaire' => $questionnaire,
        ];

        $this->sendMail($users, $subject, new ViewModel($mailParams));
    }

    /**
     * Notify all users
     */
    public function notifyRoleRequestAction()
    {
        $users = Utility::explodeIds($this->getRequest()->getParam('recipientsIds'));
        $users = $this->getEntityManager()->getRepository(\Application\Model\User::class)->findById($users);

        $emailLinkQueryString = $this->getRequest()->getParam('emailLinkQueryString');
        $applicantUser = $this->getEntityManager()->getRepository(\Application\Model\User::class)->findOneById($this->getRequest()->getParam('applicantUserId'));

        $subject = 'Role request';
        $mailParams = [
            'applicantUser' => $applicantUser,
            'emailLinkQueryString' => $emailLinkQueryString,
        ];

        $this->sendMail($users, $subject, new ViewModel($mailParams));
    }

    /**
     * Notify new comment on discussion
     */
    public function notifyCommentAction()
    {
        $commentId = $this->getRequest()->getParam('id');
        $repository = $this->getEntityManager()->getRepository(\Application\Model\Comment::class);
        $comment = $repository->findOneById($commentId);
        $discussion = $comment->getDiscussion();
        $users = $this->getEntityManager()->getRepository(\Application\Model\User::class)->getAllForCommentNotification($comment);

        $subject = 'Discussion - ' . $discussion->getName();
        $mailParams = [
            'comment' => $comment,
            'discussion' => $discussion,
        ];

        $this->sendMail($users, $subject, new ViewModel($mailParams));
    }

    /**
     * Send an activation link to specified user, so he can confirm his email is valid
     */
    public function activationLinkAction()
    {
        $userId = $this->getRequest()->getParam('id');
        $user = $this->getEntityManager()->getRepository(\Application\Model\User::class)->findOneById($userId);
        if (!$user) {
            return;
        }

        $user->generateToken();
        $this->getEntityManager()->flush();

        $subject = 'Account activation';
        $mailParams = [
            'token' => $user->getToken(),
        ];

        $this->sendMail($user, $subject, new ViewModel($mailParams));
    }

    /**
     * Send an reset password link to specified user
     */
    public function changePasswordLinkAction()
    {
        $userId = $this->getRequest()->getParam('id');
        $user = $this->getEntityManager()->getRepository(\Application\Model\User::class)->findOneById($userId);
        if (!$user) {
            return;
        }

        $user->generateToken();
        $this->getEntityManager()->flush();

        $subject = 'Reset password';
        $mailParams = [
            'token' => $user->getToken(),
        ];

        $this->sendMail($user, $subject, new ViewModel($mailParams));
    }

    /**
     * Create .eml on disk to be then send manually
     */
    public function generateWelcomeAction()
    {
        $geonames = $this->getEntityManager()->getRepository(\Application\Model\Geoname::class)->getAllWithJmpSurvey();
        $parts = $this->getEntityManager()->getRepository(\Application\Model\Part::class)->findAll();
        $fakeUser = new User();
        $fakeUser->setName('and welcome to GIMS');
        $subject = 'Welcome to GIMS';

        foreach ($geonames as $geoname) {
            echo $geoname->getName() . PHP_EOL;

            $questionnaires = $this->getEntityManager()->getRepository(\Application\Model\Questionnaire::class)->findByGeoname($geoname);
            $questionnaireIds = implode(',', array_map(function ($q) {
                        return $q->getId();
                    }, $questionnaires));

            $mailParams = [
                'geoname' => $geoname,
                'questionnaireIds' => $questionnaireIds,
                'parts' => $parts,
            ];

            $message = $this->createMessage($fakeUser, $subject, new ViewModel($mailParams));
            file_put_contents($geoname->getName() . '.eml', $message->toString());
        }
    }

    /**
     * Send a email to one or several users
     * @staticvar int $emailCount
     * @param User|User[] $users
     * @param string $subject
     * @param ViewModel $model
     */
    private function sendMail($users, $subject, ViewModel $model)
    {
        static $emailCount = 0;

        if (!is_array($users)) {
            $users = [$users];
        }

        $config = $this->getServiceLocator()->get('Config');

        foreach ($users as $user) {
            $message = $this->createMessage($user, $subject, $model);

            $email = $user->getEmail();
            $overridenBy = "";
            if (isset($config['emailOverride'])) {
                $email = $config['emailOverride'];
                $overridenBy = ' overriden by ' . $email;
            }

            if ($email) {
                $message->addTo($email, $user->getDisplayName());

                // Setup SMTP transport using LOGIN authentication
                $transport = new SmtpTransport();
                $options = new SmtpOptions($config['smtp']);
                $transport->setOptions($options);
                $transport->send($message);
            }

            $dateNow = new \DateTime();
            $dateNow = $dateNow->format('c');

            $log = 'data/logs/emails/' . $dateNow . '_' . str_pad($emailCount++, 3, '0', STR_PAD_LEFT) . '_' . $user->getEmail() . str_replace(' ', '_', $overridenBy) . '.html';
            file_put_contents($log, $message->getBodyText());

            echo 'email sent to: ' . $user->getName() . "\t" . $user->getEmail() . "\t" . $overridenBy . "\t" . $subject . PHP_EOL;
        }
    }

    /**
     * Create a message by rendering the template
     * @param User $user
     * @param string $subject
     * @param ViewModel $model
     * @return \Zend\Mail\Message
     */
    private function createMessage(User $user, $subject, ViewModel $model)
    {
        $renderer = $this->getServiceLocator()->get('ViewRenderer');

        // First render the view
        $template = 'application/email/' . $this->getRequest()->getParam('action');
        $model->setTemplate($template);
        $model->setVariable('user', $user);
        $model->setVariable('domain', $this->getServiceLocator()->get('Config')['domain']);
        $partialContent = $renderer->render($model);

        // Then inject it into layout
        $modelLayout = new ViewModel([$model->captureTo() => $partialContent]);
        $modelLayout->setTemplate('application/email/email');
        $modelLayout->setVariable('subject', $subject);
        $modelLayout->setVariable('user', $user);
        $modelLayout->setVariable('domain', $this->getServiceLocator()->get('Config')['domain']);
        $content = $renderer->render($modelLayout);

        // set Mime type html
        $htmlPart = new MimePart($content);
        $htmlPart->type = "text/html";
        $body = new MimeMessage();
        $body->setParts([$htmlPart]);

        $message = new Mail\Message();
        $message->setSubject($subject);
        $message->setBody($body);
        $message->setFrom('webmaster@gimsinitiative.org', 'GIMS');

        return $message;
    }
}
