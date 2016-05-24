<?php
namespace Craft;

class UserNotificationsService extends BaseApplicationComponent
{
    public function newUserSignup(UserModel $user)
    {
    	$settings = craft()->plugins->getPlugin('userNotifications')->getSettings();

    	$message = array(
    		'toEmail' => $user->email,
    		'subject' => $settings['newUserSubject'],
    		'body' => craft()->templates->renderString($settings['newUserBody'], array(
            	'user' => $user,
        	))
    	);

    	$this->sendEmail($message);
    }

    public function newUserAdminNotification(UserModel $user)
    {
    	$settings = craft()->plugins->getPlugin('userNotifications')->getSettings();

		$adminEmails = ArrayHelper::stringToArray($settings['newUserNotificationRecipients']);

		foreach ($adminEmails as $toEmail)
		{
	    	$message = array(
	    		'toEmail' => $toEmail,
	    		'subject' => $settings['newUserNotificationSubject'],
	    		'body' => craft()->templates->renderString($settings['newUserNotificationBody'], array(
	            	'user' => $user,
	        	))
	    	);

            $this->sendEmail($message);
    	}
    }

    public function userGroupUpdate($userId, $groupIds)
    {
        $settings = craft()->plugins->getPlugin('userNotifications')->getSettings();

        $user = craft()->users->getUserById($userId);

        $message = array(
            'toEmail' => $user->email,
            'subject' => $settings['groupUpdateSubject'],
            'body' => craft()->templates->renderString($settings['groupUpdateBody'], array(
                'user' => $user,
            ))
        );

        $this->sendEmail($message);
    }

    private function sendEmail($message)
    {
        $email = new EmailModel();
		$emailSettings = craft()->email->getSettings();

		$email->fromEmail = $emailSettings['emailAddress'];
        $email->sender = $emailSettings['emailAddress'];
        $email->fromName = $emailSettings['senderName'];

        $email->toEmail = $message['toEmail'];
        $email->subject = $message['subject'];
        $email->body = $message['body'];

        craft()->email->sendEmail($email);
    }
}