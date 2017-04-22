<?php
declare(strict_types = 1);
namespace Klapuch\Csrf;

interface Protection {
	public const NAME = '_csrf_token';

	/**
	 * Provide a protection against the CSRF attack
	 * @return string
	 */
	public function coverage(): string;

	/**
	 * Is the CSRF abused?
	 * @return bool
	 */
	public function attacked(): bool;
}