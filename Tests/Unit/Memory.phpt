<?php
declare(strict_types = 1);
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Klapuch\Csrf\Unit;

use Klapuch\Csrf;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class Memory extends Tester\TestCase {
	private $session;
	private $post;
	private $get;

	protected function setUp() {
		parent::setUp();
		[$this->session, $this->post, $this->get] = [[], [], []];
	}

	public function testGeneratedAlphaNumericProtection() {
		$protection = (new Csrf\Memory(
			$this->session,
			$this->post,
			$this->get
		))->coverage();
		Assert::match('~^[a-z0-9]+$~i', $protection);
	}

	public function testGeneratedLongEnoughProtection() {
		$protection = (new Csrf\Memory(
			$this->session,
			$this->post,
			$this->get
		))->coverage();
		Assert::true(strlen($protection) >= 20);
	}

	public function testGeneratingMultipleProtectionsWithoutOverwriting() {
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		$oldProtection = $csrf->coverage();
		$oldSession = $this->session;
		$newProtection = $csrf->coverage();
		$newSession = $this->session;
		Assert::same($oldProtection, $newProtection);
		Assert::same($oldSession, $newSession);
	}

	public function testStoringProtectionToAppropriateStorage() {
		(new Csrf\Memory(
			$this->session,
			$this->post,
			$this->get
		))->coverage();
		Assert::count(1, $this->session);
		Assert::count(0, $this->post);
		Assert::count(0, $this->get);
	}

	public function testMatchingStoredProtectionWithGenerated() {
		$protection = (new Csrf\Memory(
			$this->session,
			$this->post,
			$this->get
		))->coverage();
		Assert::contains($protection, $this->session);
	}

	public function testAbusingOnNoProvidedProtection() {
		Assert::true(
			(new Csrf\Memory(
				$this->session,
				$this->post,
				$this->get
			))->attacked()
		);
	}

	public function testAbusingOnNoMatchingProtectionInPostOrGet() {
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		$csrf->coverage();
		Assert::true($csrf->attacked());
	}

	public function testMatchedValidProtectionInPost() {
		$this->session[Csrf\Protection::NAME] = str_repeat('a', 21);
		$this->post[Csrf\Protection::NAME] = str_repeat('a', 21);
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		Assert::false($csrf->attacked());
	}

	public function testMatchedInvalidProtectionInPost() {
		$this->session[Csrf\Protection::NAME] = str_repeat('a', 22);
		$this->post[Csrf\Protection::NAME] = str_repeat('b', 23);
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		Assert::true($csrf->attacked());
	}

	public function testMatchedValidProtectionInGet() {
		$this->session[Csrf\Protection::NAME] = str_repeat('a', 21);
		$this->get[Csrf\Protection::NAME] = str_repeat('a', 21);
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		Assert::false($csrf->attacked());
	}

	public function testMatchedInvalidProtectionInGet() {
		$this->session[Csrf\Protection::NAME] = str_repeat('a', 20);
		$this->get[Csrf\Protection::NAME] = str_repeat('b', 20);
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		Assert::true($csrf->attacked());
	}

	public function testMatchedProtectionInPostAndGet() {
		$this->session[Csrf\Protection::NAME] = str_repeat('a', 20);
		$this->get[Csrf\Protection::NAME] = str_repeat('a', 20);
		$this->post[Csrf\Protection::NAME] = str_repeat('a', 20);
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		Assert::false($csrf->attacked());
	}

	public function testInsufficientProtectionInSession() {
		$this->session[Csrf\Protection::NAME] = 'abc0';
		$this->post[Csrf\Protection::NAME] = 'abc0';
		$this->get[Csrf\Protection::NAME] = 'abc0';
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		Assert::true($csrf->attacked());
	}

	public function testSecureMatching() {
		$this->session[Csrf\Protection::NAME] = '0e123454545466667676';
		$this->get[Csrf\Protection::NAME] = '0e789990909878987678';
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		Assert::true($csrf->attacked());
	}

	public function testMatchingPostWithPrecedence() {
		$this->session[Csrf\Protection::NAME] = str_repeat('a', 22);
		$this->get[Csrf\Protection::NAME] = str_repeat('b', 30);
		$this->post[Csrf\Protection::NAME] = str_repeat('a', 22);
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		Assert::false($csrf->attacked());
	}

	public function testClearingSessionAfterProperProtection() {
		$this->session[Csrf\Protection::NAME] = str_repeat('a', 22);
		$this->get[Csrf\Protection::NAME] = str_repeat('a', 22);
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		Assert::count(1, $this->session);
		Assert::false($csrf->attacked());
		Assert::count(0, $this->session);
	}

	public function testClearingSessionAfterAbusing() {
		$this->session[Csrf\Protection::NAME] = str_repeat('a', 22);
		$this->get[Csrf\Protection::NAME] = str_repeat('b', 22);
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		Assert::count(1, $this->session);
		Assert::true($csrf->attacked());
		Assert::count(0, $this->session);
	}

	public function testClearingProtectedSessionsWithoutAffectingOthers() {
		$this->session['foo'] = 'bar';
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		$csrf->coverage();
		Assert::count(2, $this->session);
		$csrf->attacked();
		Assert::count(1, $this->session);
		Assert::contains('bar', $this->session);
	}

	public function testNewProtectionAfterAbusing() {
		$csrf = new Csrf\Memory($this->session, $this->post, $this->get);
		$oldProtection = $csrf->coverage();
		$oldSession = $this->session;
		$csrf->attacked();
		$newProtection = $csrf->coverage();
		$newSession = $this->session;
		Assert::notSame($oldProtection, $newProtection);
		Assert::count(1, $newSession);
		Assert::count(1, $oldSession);
		Assert::notSame($oldSession, $newSession);
	}
}

(new Memory())->run();