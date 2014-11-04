<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;
use Application\Model\User;

class RolesRequestController extends \Application\Controller\AbstractAngularActionController
{

    /**
     * Used by contribute/request-role, send e-mails
     */
    public function sendAccessDemandAction()
    {
        $users = $this->getUsersHavingRoles();
        $queryString = '';

        foreach ($this->params()->fromQuery() as $key => $value) {
            $queryString .= $key . '=' . $value . '&';
        }
//        $emailUrl = "http://" . $this->serviceLocator->get('config')['domain'] . "/admin/roles-requests?" . $queryString . "user=" . User::getCurrentUser()->getId();
        return new JsonModel($users);
    }

    /**
     * @return array
     */
    private function getUsersHavingRoles()
    {
        $geonames = trim($this->params()->fromQuery('geonames'), ',');
        $roles = trim($this->params()->fromQuery('roles'), ',');
        $types = trim($this->params()
                           ->fromQuery('types'), ','); // clean last comma
        $types = explode(',', $types); // transform to table
        $types = array_map(function ($t) { return "'" . $t . "'";}, $types); // add quotes for sql
        $types = implode(',', $types); // concat to string

        $result = $this->getEntityManager()
                       ->getRepository('\Application\Model\User')
                       ->getAllHavingRoles($geonames, $roles, $types);

        return $this->groupByUsers($result);
    }

    /**
     * @param array $users
     * @return array
     */
    private function groupByUsers(array $users)
    {
        $groupedUsers = [];
        foreach ($users as $user) {

            // create index for user
            if (!isset($groupedUsers[$user['user_id']])) {
                $groupedUsers[$user['user_id']] = [
                    'id' => $user['user_id'],
                    'email' => $user['user_email'],
                    'name' => $user['user_name'],
                    'countries' => array(),
                ];
            }

            // create index for country
            if (!isset($groupedUsers[$user['user_id']]['countries'][$user['geoname_id']])) {
                $groupedUsers[$user['user_id']]['countries'][$user['geoname_id']] = [
                    'id' => $user['geoname_id'],
                    'questionnaires' => []
                ];
            }

            // create index for questionnaire
            if (!isset($groupedUsers[$user['user_id']]['countries'][$user['geoname_id']]['questionnaires'][$user['questionnaire_id']])) {
                $groupedUsers[$user['user_id']]['countries'][$user['geoname_id']]['questionnaires'][$user['questionnaire_id']] = [
                    'id' => $user['questionnaire_id'],
                    'survey' => $user['survey_id'],
                    'roles' => []
                ];
            }

            // add roles
            array_push($groupedUsers[$user['user_id']]['countries'][$user['geoname_id']]['questionnaires'][$user['questionnaire_id']]['roles'], $user['role_id']);

        }

        return $groupedUsers;
    }

}
