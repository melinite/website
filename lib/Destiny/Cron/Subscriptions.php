<?php
namespace Destiny\Cron;

use Destiny\Application;
use Destiny\Config;
use Destiny\Service\Twitch\Subscription;
use Destiny\Utils\Date;
use Destiny\Logger;

class Subscriptions {

	public function execute(Logger $log) {
		set_time_limit ( 480 );
		$i = 0;
		$total = 1;
		$increments = 50;
		$db = Application::getInstance ()->getDb ();
		$isSubsCleared = false;
		while ( $i < $total ) {
			set_time_limit ( 20 );
			$subscriptions = Subscription::getInstance ()->getChannelSubscriptions ( Config::$a ['twitch'] ['broadcaster'] ['user'], $increments, $i );
			if ($subscriptions == null) {
				throw new \Exception ( 'Error requesting subscriptions' );
				break;
			}
			if($isSubsCleared == false){
				$isSubsCleared = true;
				$db->update ( 'UPDATE dfl_users_twitch_subscribers SET validated = 0' );
			}
			if(!isset ( $subscriptions->_total ) || !is_numeric($subscriptions->_total)){
				throw new \Exception ( 'Error requesting subscriptions. Total: 0' );
			}
			$total = intval ( $subscriptions->_total );
			if ($total == 0) {
				throw new \Exception ( 'Error requesting subscriptions. Total: 0' );
				break;
			}
			$log->log ( 'Checked subscriptions [' . $i . ' out of ' . $total . ']' );
			foreach ( $subscriptions->subscriptions as $sub ) {
				$db->insert ( '
					INSERT INTO dfl_users_twitch_subscribers SET 
						externalId = \'{externalId}\',
						username = \'{username}\',
						displayName = \'{displayName}\',
						staff = {staff},
						subscribeDate = \'{subscribeDate}\',
						createdDate = NOW(),
						validated = 1
					ON DUPLICATE KEY UPDATE displayName=\'{displayName}\', validated = 1;
					', array (
						'externalId' => $sub->user->_id,
						'username' => $sub->user->name,
						'displayName' => $sub->user->display_name,
						'staff' => (!empty ( $sub->user->staff ) && $sub->user->staff == 1) ? '1':'0',
						'subscribeDate' => Date::getDateTime($sub->created_at, 'Y-m-d H:i:s')
				) );
				$i ++;
			}
			sleep ( 3 );
			continue;
		}
		$log->log ( 'Subscription check complete' );
	}

}