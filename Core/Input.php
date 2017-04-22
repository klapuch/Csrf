<?php
declare(strict_types = 1);
namespace Klapuch\Csrf;

/**
 * CSRF suitable for usage in input form
 */
final class Input implements Protection {
	private $origin;

	public function __construct(Protection $origin) {
		$this->origin = $origin;
	}

	public function coverage(): string {
		return sprintf(
			'<input type="hidden" name="%s" value="%s" />',
			self::NAME,
			htmlspecialchars($this->origin->coverage(), ENT_XHTML | ENT_QUOTES)
		);
	}

	public function attacked(): bool {
		return $this->origin->attacked();
	}
}