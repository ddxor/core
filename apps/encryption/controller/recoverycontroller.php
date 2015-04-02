<?php
/**
 * @author Clark Tomlinson  <clark@owncloud.com>
 * @since 2/19/15, 11:25 AM
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Encryption\Controller;


use OCA\Encryption\Recovery;
use OCP\AppFramework\Controller;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\JSON;
use OCP\AppFramework\Http\DataResponse;

class RecoveryController extends Controller {
	/**
	 * @var IConfig
	 */
	private $config;
	/**
	 * @var IL10N
	 */
	private $l;
	/**
	 * @var Recovery
	 */
	private $recovery;

	/**
	 * @param string $AppName
	 * @param IRequest $request
	 * @param IConfig $config
	 * @param IL10N $l10n
	 * @param Recovery $recovery
	 */
	public function __construct($AppName, IRequest $request, IConfig $config, IL10N $l10n, Recovery $recovery) {
		parent::__construct($AppName, $request);
		$this->config = $config;
		$this->l = $l10n;
		$this->recovery = $recovery;
	}

	public function adminRecovery($recoveryPassword, $confirmPassword, $adminEnableRecovery) {
		// Check if both passwords are the same
		if (empty($recoveryPassword)) {
			$errorMessage = $this->l->t('Missing recovery key password');
			return new DataResponse(['data' => ['message' => $errorMessage]], 500);
		}

		if (empty($confirmPassword)) {
			$errorMessage = $this->l->t('Please repeat the recovery key password');
			return new DataResponse(['data' => ['message' => $errorMessage]], 500);
		}

		if ($recoveryPassword !== $confirmPassword) {
			$errorMessage = $this->l->t('Repeated recovery key password does not match the provided recovery key password');
			return new DataResponse(['data' => ['message' => $errorMessage]], 500);
		}

		if (isset($adminEnableRecovery) && $adminEnableRecovery === '1') {
			if ($this->recovery->enableAdminRecovery($recoveryPassword)) {
				return new DataResponse(['status'	=>'success', 'data' => array('message' => $this->l->t('Recovery key successfully enabled'))]);
			}
			return new DataResponse(['data' => array('message' => $this->l->t('Could not enable recovery key. Please check your recovery key password!'))]);
		} elseif (isset($adminEnableRecovery) && $adminEnableRecovery === '0') {
			if ($this->recovery->disableAdminRecovery($recoveryPassword)) {
				return new DataResponse(['data' => array('message' => $this->l->t('Recovery key successfully disabled'))]);
			}
			return new DataResponse(['data' => array('message' => $this->l->t('Could not disable recovery key. Please check your recovery key password!'))]);
		}
	}

	public function changeRecoveryPassword($newPassword, $oldPassword, $confirmPassword) {
		//check if both passwords are the same
		if (empty($oldPassword)) {
			$errorMessage = $this->l->t('Please provide the old recovery password');
			return new DataResponse(array('data' => array('message' => $errorMessage)));
		}

		if (empty($newPassword)) {
			$errorMessage = $this->l->t('Please provide a new recovery password');
			return new DataResponse (array('data' => array('message' => $errorMessage)));
		}

		if (empty($confirmPassword)) {
			$errorMessage = $this->l->t('Please repeat the new recovery password');
			return new DataResponse(array('data' => array('message' => $errorMessage)));
		}

		if ($newPassword !== $confirmPassword) {
			$errorMessage = $this->l->t('Repeated recovery key password does not match the provided recovery key password');
			return new DataResponse(array('data' => array('message' => $errorMessage)));
		}

		$result = $this->recovery->changeRecoveryKeyPassword($newPassword, $oldPassword);

		if ($result) {
			return new DataResponse(array('status' => 'success' ,'data' => array('message' => $this->l->t('Password successfully changed.'))));
		} else {
			return new DataResponse(array('data' => array('message' => $this->l->t('Could not change the password. Maybe the old password was not correct.'))));
		}
	}

	public function userRecovery($userEnableRecovery) {
		if (isset($userEnableRecovery) && ($userEnableRecovery === '0' || $userEnableRecovery === '1')) {
			$userId = $this->user->getUID();
			if ($userEnableRecovery === '1') {
				// Todo xxx figure out if we need keyid's here or what.
				return $this->recovery->addRecoveryKeys();
			}
			// Todo xxx see :98
			return $this->recovery->removeRecoveryKeys();
		}
	}

}
