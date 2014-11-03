<?php

/**
 * ownCloud - dashboard
 *
 * @author Patrick Paysant <ppaysant@linagora.com>
 * @copyright 2014 CNRS DSI
 * @license This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\dashboard\Service;

class GroupsService
{

    protected $historyByGroupMapper;

    public function __construct(\OCA\Dashboard\Db\HistoryByGroupMapper $historyByGroupMapper)
    {
        $this->historyByGroupMapper = $historyByGroupMapper;
    }

    /**
     * Returns a list of admin and normal groups
     * @param string $search
     * @return array
     */
    public function groups($search='')
    {
        $groupManager = \OC_Group::getManager();

        $isAdmin = \OC_User::isAdminUser(\OCP\User::getUser());

        $groupsInfo = new \OC\Group\MetaData(\OC_User::getUser(), $isAdmin, $groupManager);
        $groupsInfo->setSorting($groupsInfo::SORT_USERCOUNT);
        list($adminGroup, $groups) = $groupsInfo->get($search);

        return array(
            'adminGroups' => $adminGroup,
            'groups' => $groups,
        );
    }

    /**
     * Verify if group stats are enabled (see general settings screen, "Dashboard" section)
     * @return boolean
     */
    public static function isGroupsEnabled()
    {
        $appConfig = \OC::$server->getAppConfig();
        $result = $appConfig->getValue('dashboard', 'dashboard_groups_enabled', 'no');

        return $result;
    }

    /**
     * Returns the list of stat's enabled groups
     * @param int $range Number of days from today you want to get the groups
     * @return array
     */
    public function statsEnabledGroups($range = 30)
    {
        // naive approach...
        // $appConfig = \OC::$server->getAppConfig();
        // $result = $appConfig->getValue('dashboard', 'dashboard_group_list', '');
        // return $result;

        // as admin may change the enabled groups list, it's probably better to get real group list instead of appconfig group list
        // Searched in history (for given range) which gid are presents
        $groups = array();

        $datetime = new \DateTime();
        $datetime->sub(new \dateInterval('P' . (int)$range . 'D'));
        $datetime->setTime(23, 59, 59);
        $groups = $this->historyByGroupMapper->findAllGidFrom($datetime);
$f = fopen('/tmp/truc.log', 'a');
fputs($f, print_r($groups, true) . "\n");
fclose($f);
        return $groups;
    }

}
