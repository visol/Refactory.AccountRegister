<?php
namespace Refactory\AccountRegister\Domain\Factory;

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
 * A factory to conveniently create User models
 *
 * @Flow\Scope("singleton")
 */
class UserFactory {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\AccountFactory
	 */
	protected $accountFactory;

	/**
	 * @var string
	 * @Flow\Inject(setting="model", package="Refactory.Register")
	 */
	protected $model;

	/**
	 * Creates a User with the given information
	 *
	 * The User is not added to the repository, the caller has to add the
	 * User account to the AccountRepository and the User to the
	 * PartyRepository to persist it.
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $firstName
	 * @param string $lastName
	 * @param string $email
	 * @param array $roleIdentifiers
	 * @return \TYPO3\Party\Domain\Model\Person
	 */
	public function create($username, $password, $firstName, $lastName, $email, array $roleIdentifiers = NULL) {
		$user = new $this->model;
		$name = new \TYPO3\Party\Domain\Model\PersonName('', $firstName, '', $lastName, '', $username);

		$user->setName($name);

		$electronicAddress = new \TYPO3\Party\Domain\Model\ElectronicAddress();
		$electronicAddress->setType('Email');
		$electronicAddress->setIdentifier($email);

		$user->addElectronicAddress($electronicAddress);
		$user->setPrimaryElectronicAddress($electronicAddress);

		$account = $this->accountFactory->createAccountWithPassword($username, $password, $roleIdentifiers, 'DefaultProvider');
		$user->addAccount($account);

		return $user;
	}
}