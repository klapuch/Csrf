<?php
declare(strict_types = 1);
namespace Klapuch\Csrf;

/**
 * Fake
 */
final class FakeProtection implements Protection {
	private $protection;
	private $abused;

	public function __construct(string $protection = null, bool $abused = null) {
		$this->protection = $protection;
		$this->abused = $abused;
	}

	public function coverage(): string {
		return $this->protection;
	}

	public function attacked(): bool {
		return $this->abused;
	}
}