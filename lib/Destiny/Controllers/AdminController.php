<?php
namespace Destiny\Controllers;

use Destiny\Common\Session;
use Destiny\Common\ViewModel;
use Destiny\Common\Annotation\Controller;
use Destiny\Common\Annotation\Route;
use Destiny\Common\Annotation\HttpMethod;
use Destiny\Common\Annotation\Secure;
use Destiny\Common\Annotation\Transactional;
use Destiny\Common\Exception;
use Destiny\Common\Application;
use Destiny\Common\Scheduler;
use Destiny\Common\Response;
use Destiny\Common\Utils\Http;
use Destiny\Common\MimeType;
use Destiny\Common\Config;
use Destiny\Common\User\UserService;
use Destiny\Commerce\SubscriptionsService;
use Destiny\Chat\ChatIntegrationService;
use Destiny\Common\Utils\FilterParams;

/**
 * @Controller
 */
class AdminController {

	/**
	 * @Route ("/admin")
	 * @Secure ({"ADMIN"})
	 * @HttpMethod ({"GET","POST"})
	 *
	 * @param array $params        	
	 * @param ViewModel $model        	
	 * @return string
	 */
	public function admin(array $params, ViewModel $model) {
		if (empty ( $params ['page'] )) {
			$params ['page'] = 1;
		}
		if (empty ( $params ['size'] )) {
			$params ['size'] = 20;
		}
		$model->title = 'Administration';
		$model->user = Session::getCredentials ()->getData ();
		$model->users = UserService::instance ()->listUsers ( intval ( $params ['size'] ), intval ( $params ['page'] ) );
		$model->size = $params ['size'];
		$model->page = $params ['page'];
		return 'admin/admin';
	}

	/**
	 * @Route ("/admin/cron")
	 * @Secure ({"ADMIN"})
	 * @Transactional
	 *
	 * @param array $params        	
	 * @throws Exception
	 */
	public function adminCron(array $params) {
		if (! isset ( $params ['id'] ) || empty ( $params ['id'] )) {
			throw new Exception ( 'Action id required.' );
		}
		set_time_limit ( 180 );
		$log = Application::instance ()->getLogger ();
		
		$response = array ();
		$scheduler = new Scheduler ( Config::$a ['scheduler'] );
		$scheduler->setLogger ( $log );
		$scheduler->loadSchedule ();
		$scheduler->executeTaskByName ( $params ['id'] );
		$response ['message'] = sprintf ( 'Execute %s', $params ['id'] );
		
		$response = new Response ( Http::STATUS_OK, json_encode ( $response ) );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		return $response;
	}
	
	/**
	 * @Route ("/admin/subscribers")
	 * @Secure ({"ADMIN"})
	 *
	 * @param array $params        	
	 * @throws Exception
	 */
	public function adminSubscribers(array $params, ViewModel $model) {
		$subService = SubscriptionsService::instance ();
		$model->subscribersT3 = $subService->getSubscriptionsByTier ( 3 );
		$model->subscribersT2 = $subService->getSubscriptionsByTier ( 2 );
		$model->subscribersT1 = $subService->getSubscriptionsByTier ( 1 );
		return 'admin/subscribers';
	}

	/**
	 * @Route ("/admin/bans")
	 * @Secure ({"ADMIN"})
	 *
	 * @param array $params        	
	 * @throws Exception
	 */
	public function adminBans(array $params, ViewModel $model) {
		$chatService = ChatIntegrationService::instance ();
		$model->activeBans = $chatService->getActiveBans();
		return 'admin/bans';
	}

	/**
	 * @Route ("/admin/bans/purgeall")
	 * @Secure ({"ADMIN"})
	 *
	 * @param array $params        	
	 * @throws Exception
	 */
	public function adminPurgeBans(array $params, ViewModel $model) {
		$chatService = ChatIntegrationService::instance ();
		$chatService->purgeBans();
		return 'redirect: /admin/bans';
	}

	/**
	 * @Route ("/admin/user/find")
	 * @Secure ({"ADMIN"})
	 *
	 * @param array $params        	
	 */
	public function adminUserFind(array $params) {
		FilterParams::isRequired($params, 's');
		$users = UserService::instance ()->findUsers ( $params ['s'], 10 );
		$response = new Response ( Http::STATUS_OK );
		$response->addHeader ( Http::HEADER_CONTENTTYPE, MimeType::JSON );
		$response->setBody ( json_encode ( $users ) );
		return $response;
	}

}
