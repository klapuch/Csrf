<?php
declare(strict_types = 1);
namespace Klapuch\Csrf;

/**
 * CSRF stored across SESSION, POST and GET server variables
 */
final class StoredCsrf implements Csrf {
	private const TOKEN_LENGTH = 20;
	private const INVALID_TOKEN = '';
	private $session;
	private $post;
	private $get;

	public function __construct(array &$session, array $post, array $get) {
		$this->session = &$session;
		$this->post = $post;
		$this->get = $get;
	}

	public function protection(): string {
		return $this->session[self::NAME] = $this->session[self::NAME] ?? $this->token();
	}

	public function abused(): bool {
		$token = $this->session[self::NAME] ?? self::INVALID_TOKEN;
		unset($this->session[self::NAME]);
		return !$this->solid($token) || !hash_equals($token, $this->twin());
	}

	/**
	 * Random generated secure token
	 * @return string
	 */
	private function token(): string {
		$token = '';
		while(!$this->solid($token)) {
			$token .= preg_replace(
				'~[^\w\d]~',
				'',
				base64_encode(random_bytes(13))
			);
		}
		return $token;
	}

	/**
	 * Is the token strong enough?
	 * @param string $token
	 * @return bool
	 */
	private function solid(string $token): bool {
		return $token && strlen($token) >= self::TOKEN_LENGTH;
	}

	/**
	 * Twin of the generated token
	 * @return string
	 */
	private function twin(): string {
		return ($this->get + $this->post)[self::NAME] ?? self::INVALID_TOKEN;
	}
}