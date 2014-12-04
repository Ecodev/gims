<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;
use Application\Model\User;
use Application\Utility;

class RolesRequestController extends \Application\Controller\AbstractAngularActionController
{

    /**
     * Used by contribute/request-role, send e-mails
     */
    public function requestRolesAction()
    {
        $users = array_map(function($user) {
            return $user['user_id'];
        }, $this->getUsersHavingRoles());

        $queryString = '';

        foreach ($this->params()->fromQuery() as $key => $value) {
            $queryString .= $key . '=' . $value . '&';
        }

        $emailParams = $queryString . "user=" . User::getCurrentUser()->getId();
        Utility::executeCliCommand('email', 'notifyRoleRequest', implode(',', $users), User::getCurrentUser()->getId(), $emailParams);

        return new JsonModel($users);
    }

    /**
     * Used by admin/roles-requests to retrieve users
     */
    public function getRequestsAction()
    {
        $adminPermissions = $this->getUsersHavingRoles(User::getCurrentUser());
        $adminPermissions = $this->groupByGeoname($adminPermissions);

        $applicantUser = $this->getEntityManager()
                              ->getRepository('\Application\Model\User')
                              ->findOneById($this->params()->fromQuery('user'));
        $applicantUserPermissions = $this->getUsersHavingRoles($applicantUser);
        $applicantUserPermissions = $this->groupByGeoname($applicantUserPermissions);

        $result = array(
            'applicant' => $applicantUserPermissions,
            'admin' => $adminPermissions
        );

        return new JsonModel($result);
    }

    /**
     * @param User $user
     * @return array
     */
    private function getUsersHavingRoles(User $user = null)
    {
        $geonames = Utility::explodeIds($this->params()->fromQuery('geonames'));
        $roles = Utility::explodeIds($this->params()->fromQuery('roles'));
        $types = Utility::explodeIds($this->params()->fromQuery('types'));

        $result = $this->getEntityManager()->getRepository('\Application\Model\User')->getAllHavingRoles($geonames, $roles, $types, $user);

        return $result;
    }

    /**
     * @param array $data
     * @return array
     */
    private function groupByGeoname(array $data)
    {
        $geonames = [];
        foreach ($data as $data) {

            // create index for country
            if (!isset($geonames[$data['geoname_id']])) {
                $geonames[$data['geoname_id']] = [
                    'name' => $data['geoname_name'],
                ];
            }

            // create index for questionnaire
            if (!isset($geonames[$data['geoname_id']]['questionnaires'][$data['questionnaire_id']])) {
                $geonames[$data['geoname_id']]['questionnaires'][$data['questionnaire_id']] = [
                    'survey' => [
                        'id' => $data['survey_id'],
                        'code' => $data['survey_code'],
                    ],
                ];
            }

            // create relation and assign role
            // user_survey and user_questionnaire are reported @questionnaire level to simulate inheritance, only questionnaire are affected by application
            $geonames[$data['geoname_id']]['questionnaires'][$data['questionnaire_id']]['roles'][$data['role_id']] = [
                'name' => $data['role_name'],
                'userRelation' => [
                    'id' => $data['relation_id'],
                    'type' => $data['relation_type'],
                ]
            ];

            // add modifier (modifier or creator have the same name) to relation
            if ($data['relation_modifier_email']) {
                $geonames[$data['geoname_id']]['questionnaires'][$data['questionnaire_id']]['roles'][$data['role_id']]['userRelation']['modifier'] = [
                    'name' => $data['relation_modifier_name'],
                    'email' => $data['relation_modifier_email']
                ];
            } elseif ($data['relation_creator_email']) {
                $geonames[$data['geoname_id']]['questionnaires'][$data['questionnaire_id']]['roles'][$data['role_id']]['userRelation']['modifier'] = [
                    'name' => $data['relation_creator_name'],
                    'email' => $data['relation_creator_email']
                ];
            }
        }

        return $geonames;
    }

}
