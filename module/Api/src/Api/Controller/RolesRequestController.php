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

        $emailParams = "'" . $queryString . "user=" . User::getCurrentUser()->getId() . "'";
        Utility::executeCliCommand('email notifyRoleRequest ' . implode(',', $users) . ' ' . User::getCurrentUser()->getId() . ' ' . $emailParams);

        return new JsonModel($users);
    }

    /**
     * Used by admin/roles-requests to retrieve users
     */
    public function getRequestsAction()
    {
        $result = $this->getUsersHavingRoles(User::getCurrentUser());

        return new JsonModel($this->groupByGeoname($result));
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
     * @param array $users
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
                    'questionnaires' => []
                ];
            }

            // create index for questionnaire
            if (!isset($geonames[$data['geoname_id']]['questionnaires'][$data['questionnaire_id']])) {
                $geonames[$data['geoname_id']]['questionnaires'][$data['questionnaire_id']] = [
                    'survey' => [
                        'id' => $data['survey_id'],
                        'code' => $data['survey_code'],
                    ],
                    'roles' => []
                ];
            }

            // add roles
            $role = [
                'id' => $data['role_id'],
                'name' => $data['role_name']
            ];
            array_push($geonames[$data['geoname_id']]['questionnaires'][$data['questionnaire_id']]['roles'], $role);
        }

        return $geonames;
    }

}
