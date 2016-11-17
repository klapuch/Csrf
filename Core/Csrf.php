<?php
declare(strict_types = 1);
namespace Klapuch\Csrf;

interface Csrf {
	public const NAME = '_csrf_token';

	/**
	 * Provide a protection against the CSRF attack
	 * @return string
	 */
	public function protection(): string;

	/**
	 * Is the CSRF abused?
	 * @return bool
	 */
	public function abused(): bool;
}