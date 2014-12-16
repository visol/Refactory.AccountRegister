<?php
namespace Refactory\AccountRegister\Service;

/*                                                                                  *
 * This script belongs to the TYPO3 Flow package "Refactory.AccountRegister".       *
 *                                                                                  *
 * It is free software; you can redistribute it and/or modify it under              *
 * the terms of the GNU General Public License, either version 3 of the             *
 * License, or (at your option) any later version.                                  *
 *                                                                                  *
 * The TYPO3 project - inspiring people to share!                                   *
 *                                                                                  */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Security\Account;

/**
 * An AccountManagementService service
 *
 */
class AccountManagementService {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountRepository
	 */
	protected $accountRepository;

	/**
	 * @var \TYPO3\Flow\Security\Cryptography\HashService
	 * @Flow\Inject
	 */
	protected $hashService;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 * @return void
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Set a new password for the given account
	 *
	 * This allows for setting a new password for an existing user account.
	 *
	 * @param Account $account
	 * @param $password
	 * @param string $passwordHashingStrategy
	 *
	 * @return boolean
	 */
	public function resetPassword(Account $account, $password, $passwordHashingStrategy = 'default') {
		$account->setCredentialsSource($this->hashService->hashPassword($password, $passwordHashingStrategy));
		$this->accountRepository->update($account);
		return TRUE;
	}
}