<?php
declare(strict_types = 1);
namespace Klapuch\Csrf;

/**
 * CSRF suitable for usage in input form
 */
final class CsrfInput implements Csrf {
	private $origin;

	public function __construct(Csrf $origin) {
		$this->origin = $origin;
	}

	public function protection(): string {
		return sprintf(
			'<input type="hidden" name="%s" value="%s" />',
			self::NAME,
			htmlspecialchars(
				$this->origin->protection(),
				ENT_XHTML | ENT_QUOTES
			)
		);
	}

	public function abused(): bool {
		return $this->origin->abused();
	}
}