<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Klapuch\Unit\Csrf;

use Klapuch\Csrf;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class StoredCsrf extends Tester\TestCase {
	private $session;
	private $post;
	private $get;

	protected function setUp() {
		parent::setUp();
		[$this->session, $this->post, $this->get] = [[], [], []];
	}

	public function testGeneratedAlphaNumericProtection() {
		$protection = (new Csrf\StoredCsrf(
			$this->session,
			$this->post,
			$this->get
		))->protection();
		Assert::match('~^[a-z0-9]+$~i', $protection);
		Assert::true(strlen($protection) >= 20);
	}

	public function testGeneratingMultipleProtectionsWithoutOverwriting() {
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		$oldProtection = $csrf->protection();
		$oldSession = $this->session;
		$newProtection = $csrf->protection();
		$newSession = $this->session;
		Assert::same($oldProtection, $newProtection);
		Assert::same($oldSession, $newSession);
	}

	public function testStoringProtection() {
		(new Csrf\StoredCsrf(
			$this->session,
			$this->post,
			$this->get
		))->protection();
		Assert::count(1, $this->session);
		Assert::count(0, $this->post);
		Assert::count(0, $this->get);
	}

	public function testMatchingStoredProtectionWithGenerated() {
		$protection = (new Csrf\StoredCsrf(
			$this->session,
			$this->post,
			$this->get
		))->protection();
		Assert::contains($protection, $this->session);
	}

	public function testNoProvidedProtection() {
		Assert::true(
			(new Csrf\StoredCsrf(
				$this->session,
				$this->post,
				$this->get
			))->abused()
		);
	}

	public function testNoMatchingProtectionInPostOrGet() {
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		$csrf->protection();
		Assert::true($csrf->abused());
	}

	public function testMatchedValidProtectionInPost() {
		$this->session[Csrf\Csrf::NAME] = str_repeat('a', 21);
		$this->post[Csrf\Csrf::NAME] = str_repeat('a', 21);
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		Assert::false($csrf->abused());
	}

	public function testMatchedInvalidProtectionInPost() {
		$this->session[Csrf\Csrf::NAME] = str_repeat('a', 22);
		$this->post[Csrf\Csrf::NAME] = str_repeat('b', 23);
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		Assert::true($csrf->abused());
	}

	public function testMatchedValidProtectionInGet() {
		$this->session[Csrf\Csrf::NAME] = str_repeat('a', 21);
		$this->get[Csrf\Csrf::NAME] = str_repeat('a', 21);
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		Assert::false($csrf->abused());
	}

	public function testMatchedInvalidProtectionInGet() {
		$this->session[Csrf\Csrf::NAME] = str_repeat('a', 20);
		$this->get[Csrf\Csrf::NAME] = str_repeat('b', 20);
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		Assert::true($csrf->abused());
	}

	public function testMatchedProtectionInPostAndGet() {
		$this->session[Csrf\Csrf::NAME] = str_repeat('a', 20);
		$this->get[Csrf\Csrf::NAME] = str_repeat('a', 20);
		$this->post[Csrf\Csrf::NAME] = str_repeat('a', 20);
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		Assert::false($csrf->abused());
	}

	public function testInsufficientProtectionInSession() {
		$this->session[Csrf\Csrf::NAME] = 'abc0';
		$this->post[Csrf\Csrf::NAME] = 'abc0';
		$this->get[Csrf\Csrf::NAME] = 'abc0';
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		Assert::true($csrf->abused());
	}

	public function testSecureMatching() {
		$this->session[Csrf\Csrf::NAME] = '0e123454545466667676';
		$this->get[Csrf\Csrf::NAME] = '0e789990909878987678';
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		Assert::true($csrf->abused());
	}

	public function testMatchingGetWithPrecedence() {
		$this->session[Csrf\Csrf::NAME] = str_repeat('a', 22);
		$this->get[Csrf\Csrf::NAME] = str_repeat('a', 22);
		$this->post[Csrf\Csrf::NAME] = str_repeat('b', 30);
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		Assert::false($csrf->abused());
	}

	public function testClearingSessionAfterProperProtection() {
		$this->session[Csrf\Csrf::NAME] = str_repeat('a', 22);
		$this->get[Csrf\Csrf::NAME] = str_repeat('a', 22);
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		Assert::count(1, $this->session);
		Assert::false($csrf->abused());
		Assert::count(0, $this->session);
	}

	public function testClearingSessionAfterAbusing() {
		$this->session[Csrf\Csrf::NAME] = str_repeat('a', 22);
		$this->get[Csrf\Csrf::NAME] = str_repeat('b', 22);
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		Assert::count(1, $this->session);
		Assert::true($csrf->abused());
		Assert::count(0, $this->session);
	}

	public function testClearingProtectedSessionsWithoutAffectingOthers() {
		$this->session['foo'] = 'bar';
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		$csrf->protection();
		Assert::count(2, $this->session);
		$csrf->abused();
		Assert::count(1, $this->session);
		Assert::contains('bar', $this->session);
	}

	public function testNewProtectionAfterAbusing() {
		$csrf = new Csrf\StoredCsrf($this->session, $this->post, $this->get);
		$oldProtection = $csrf->protection();
		$oldSession = $this->session;
		$csrf->abused();
		$newProtection = $csrf->protection();
		$newSession = $this->session;
		Assert::notSame($oldProtection, $newProtection);
		Assert::count(1, $newSession);
		Assert::count(1, $oldSession);
		Assert::notSame($oldSession, $newSession);
	}
}

(new StoredCsrf())->run();