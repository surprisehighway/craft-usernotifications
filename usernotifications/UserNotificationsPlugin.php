<?php
/**
 * User Notifications plugin for Craft CMS
 *
 * Simple notifications for user signups.
 *
 * @author    Mike Kroll
 * @copyright Copyright (c) 2016 Surprise Highway
 * @link      http://surprisehighway.com
 * @package   UserNotifications
 * @since     1.0.0
 */

namespace Craft;

class UserNotificationsPlugin extends BasePlugin
{
    /**
     * @return mixed
     */
    public function getName()
    {
         return Craft::t('User Notifications');
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return Craft::t('Simple notifications for user signups.');
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @return string
     */
    public function getSchemaVersion()
    {
        return '1.0.0';
    }

    /**
     * @return string
     */
    public function getDocumentationUrl()
    {
        return 'https://github.com/surprisehighway/craft-usernotifications/blob/master/README.md';
    }

    /**
     * @return string
     */
    public function getReleaseFeedUrl()
    {
        return 'https://raw.githubusercontent.com/surprisehighway/craft-usernotifications/master/releases.json';
    }

    /**
     * @return string
     */
    public function getDeveloper()
    {
        return 'Surprise Highway';
    }

    /**
     * @return string
     */
    public function getDeveloperUrl()
    {
        return 'http://surprisehighway.com';
    }

    /**
     * @return bool
     */
    public function hasCpSection()
    {
        return false;
    }

    /**
     * @return mixed
     */
    public function getSettingsHtml()
    {
       return craft()->templates->render('usernotifications/settings', array(
           'settings' => $this->getSettings()
       ));
    }

    /**
     * @return array
     */
    protected function defineSettings()
    {
        return array(
            'newUserEnabled' => array(AttributeType::Bool, 'default' => 0),
            'newUserSubject' => array(AttributeType::String, 'default' => ''),
            'newUserBody' => array(AttributeType::String, 'default' => ''),
            'newUserNotificationEnabled' => array(AttributeType::Bool, 'default' => 0),
            'newUserNotificationRecipients' => array(AttributeType::String, 'default' => ''),
            'newUserNotificationSubject' => array(AttributeType::String, 'Subject', 'default' => ''),
            'newUserNotificationBody' => array(AttributeType::String, 'default' => ''),
            'groupUpdateEnabled' => array(AttributeType::Bool, 'default' => 0),
            'groupUpdate' => array(AttributeType::Mixed, 'default' => null),
            'groupUpdateSubject' => array(AttributeType::String, 'default' => ''),
            'groupUpdateBody' => array(AttributeType::String, 'default' => ''),
        );
    }

    /**
     * @return mixed
     */
    public function init()
    {
        craft()->on('users.onBeforeSaveUser', function(Event $event) {
            $settings = $this->getSettings();
            $user = $event->params['user'];
            $isNewUser = $event->params['isNewUser'];

            if($isNewUser)
            {
                if($settings['newUserEnabled']) 
                {
                    craft()->userNotifications->newUserSignup($user);
                }

                if($settings['newUserNotificationEnabled']) 
                {
                    craft()->userNotifications->newUserAdminNotification($user);
                }
            }
        });

        craft()->on('userGroups.onBeforeAssignUserToGroups', function(Event $event) {
            $settings = $this->getSettings();
            $userId = $event->params['userId'];
            $groupIds = $event->params['groupIds'];

            $updateGroups = $settings['groupUpdate'];
            $inUpdateGroups = false;

            foreach($groupIds as $id) 
            {
                if(in_array($id, $updateGroups)) 
                {
                    $inUpdateGroups = true;
                }
            }

            if($settings['groupUpdateEnabled'] && $inUpdateGroups) 
            {
                craft()->userNotifications->userGroupUpdate($userId, $groupIds);
            }

        });
    }
}