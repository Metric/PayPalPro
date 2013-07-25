<?php

/**
 * Special Virtuemart PayPal Express Login Authenticator
 * Based on the PayPal Express payerID
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
 
jimport('joomla.event.plugin');
jimport('joomla.error.log');

class plgAuthenticationvirtuemartpaypal extends JPlugin
{
    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param object $subject The object to observe
     * @since 1.5
     */
    function plgAuthenticationvirtuemartpaypal(& $subject) {
        parent::__construct($subject);
    }
 
    /**
     * Checks to see if the payerID matches a previous payerID in the virtuemart DB
     * We only care about the Username for Joomla, but the password is really the payerID
	 * That is passed from Virtuemart. The payerID is then used to check the ID from 
	 * Joomla with the ID in the Virtuemart user_info. We do this just to make sure it is
	 * The correct user trying to login and not someone else
	 *
     * @access    public
     * @param    string    $username    Username for authentication
     * @param    string    $password    Password for authentication
     * @param    object    $response    Authentication response object
     * @return    boolean
     * @since 1.5
     */
    function onAuthenticate( $credentials, $options, &$response  )
    {
        $db =& JFactory::getDBO();
        $query = 'SELECT * FROM `#__users` WHERE username=' . $db->Quote( $credentials['username'] );
        $db->setQuery( $query );
        $result = $db->loadObject();
 
		$options = array(
			'format' => "{DATE}\t{TIME}\t{USER_ID}\t{COMMENT}"
		);

		$log = &JLog::getInstance();
 
        if (!$result) {
            $response->status = JAUTHENTICATE_STATUS_FAILURE;
            $response->error_message = 'User does not exist';
			$log->addEntry(array('comment' => 'User does not exist'));
        }
		else
		{
			$log->addEntry(array('comment' => 'Found User'));
			$query = 'SELECT * FROM `#__vm_user_info` WHERE extra_field_3 ='.$db->Quote($credentials['password']).' AND user_id ='.$db->Quote($result->id).' ORDER by mdate DESC';
			$db->setQuery($query);
			$result2 = $db->loadObject();
			
			//Check to see if we found a user with the User Id and Payer Id match
			//If so login the user and if not do not login.
			if(!$result2)
			{
				$log->addEntry(array('comment' => 'No user found with Payer ID in Virtuemart'));
				$response->status = JAUTHENTICATE_STATUS_FAILURE;
				$response->error_message = 'No user found with Payer ID in Virtuemart';
			}
			else
			{
				$log->addEntry(array('comment' => 'User Found Logging in...'));
				$response->email = $result->email;
				$response->status = JAUTHENTICATE_STATUS_SUCCESS;
			}
        }
    }
}
?>
