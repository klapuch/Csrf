<?php
declare(strict_types = 1);
namespace Klapuch\Csrf;

/**
 * CSRF suitable for usage in link
 */
final class Link implements Protection {
	private $origin;

	public function __construct(Protection $origin) {
		$this->origin = $origin;
	}

	public function coverage(): string {
		return http_build_query([self::NAME => $this->origin->coverage()]);
	}

	public function attacked(): bool {
		return $this->origin->attacked();
	}
}