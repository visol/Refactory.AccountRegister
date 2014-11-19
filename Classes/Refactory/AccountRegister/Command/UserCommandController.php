<?php
namespace Refactory\AccountRegister\Command;

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

/**
 * Setup command controller
 *
 * @Flow\Scope("singleton")
 */
class UserCommandController extends \TYPO3\Flow\Cli\CommandController {

	/**
	 * @var \TYPO3\Flow\Security\AccountRepository
	 * @Flow\Inject
	 */
	protected $accountRepository;

	/**
	 * @var \TYPO3\Party\Domain\Repository\PartyRepository
	 * @Flow\Inject
	 */
	protected $partyRepository;

	/**
	 * @Flow\Inject
	 * @var \Refactory\Login\Domain\Factory\UserFactory
	 */
	protected $userFactory;

	/**
	 * @var \Refactory\Login\Service\AccountManagementService
	 * @Flow\Inject
	 */
	protected $accountManagementService;

	/**
	 * Create a user
	 *
	 * @param string $username Username
	 * @param string $password Password
	 * @param string $email Email
	 * @Flow\Validate(argumentName="email", type="\TYPO3\Flow\Validation\Validator\EmailAddressValidator")
	 * @param string $roles A comma separated list of roles to assign
	 * @return void
	 */
	public function createCommand($username, $password, $email, $roles = NULL) {
		$account = $this->accountRepository->findByAccountIdentifierAndAuthenticationProviderName($username, 'DefaultProvider');
		if ($account instanceof \TYPO3\Flow\Security\Account) {
			$this->outputLine('The Username <b>"%s"</b> is already in use.', array($username));
			return;
		}

		if (empty($roles)) {
			$roleIdentifiers = array('Refactory.Login:User');
		} else {
			$roleIdentifiers = \TYPO3\Flow\Utility\Arrays::trimExplode(',', $roles);
			foreach ($roleIdentifiers as &$role) {
				if (strpos($role, '.') === FALSE) {
					$role = 'TYPO3.Flow:' . $role;
				}
			}
		}

		try {
			$user = $this->userFactory->create($username, $password, $firstName = 'John', $lastName = 'Doe', $email, $roleIdentifiers);
			$this->partyRepository->add($user);
			$accounts = $user->getAccounts();
			foreach ($accounts as $account) {
				$this->accountRepository->add($account);
			}

			$this->outputLine('Created user <b>"%s"</b>.', array($username));
		} catch (\TYPO3\Flow\Security\Exception\NoSuchRoleException $exception) {
			$this->outputLine($exception->getMessage());
			$this->quit(1);
		}
	}

	/**
	 * Set a new password for the given account
	 *
	 * This allows for setting a new password for an existing user account.
	 *
	 * @param string $username Username of the account to modify
	 * @param string $password The new password
	 * @param string $authenticationProvider The name of the authentication provider to use
	 * @return void
	 */
	public function setPasswordCommand($username, $password, $authenticationProvider = 'DefaultProvider') {
		$account = $this->accountManagementService->getAccount($username, $authenticationProvider);
		if (!$account instanceof \TYPO3\Flow\Security\Account) {
			$this->outputLine('User "%s" does not exists.', array($username));
			$this->quit(1);
		}
		$this->accountManagementService->resetPassword($account, $password);
		$this->outputLine('The new password for user "%s" was set.', array($username));
	}
}