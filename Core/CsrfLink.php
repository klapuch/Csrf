<?php
declare(strict_types = 1);
namespace Klapuch\Csrf;

/**
 * CSRF suitable for usage in link
 */
final class CsrfLink implements Csrf {
	private $origin;

	public function __construct(Csrf $origin) {
		$this->origin = $origin;
	}

	public function protection(): string {
		return http_build_query([self::NAME => $this->origin->protection()]);
	}

	public function abused(): bool {
		return $this->origin->abused();
	}
}