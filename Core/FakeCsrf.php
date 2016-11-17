<?php
declare(strict_types = 1);
namespace Klapuch\Csrf;

final class FakeCsrf implements Csrf {
	private $protection;
	private $abused;

	public function __construct(
		string $protection = null,
		bool $abused = null
	) {
		$this->protection = $protection;
		$this->abused = $abused;
	}

	public function protection(): string {
		return $this->protection;
	}

	public function abused(): bool {
		return $this->abused;
	}
}