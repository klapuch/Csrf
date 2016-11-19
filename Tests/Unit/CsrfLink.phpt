<?php
/**
 * @testCase
 * @phpVersion > 7.1
 */
namespace Klapuch\Csrf\Unit;

use Klapuch\Csrf;
use Tester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

final class CsrfLink extends Tester\TestCase {
	public function testProtectionAsPartOfQuery() {
		Assert::contains(
			'=abc123',
			(new Csrf\CsrfLink(
				new Csrf\FakeCsrf('abc123')
			))->protection()
		);
	}

	public function testProtectionWithKeyValue() {
		Assert::match(
			'~^_[\S]+=[\w]+$~',
			(new Csrf\CsrfLink(
				new Csrf\FakeCsrf('abc')
			))->protection()
		);
	}

	public function testProtectionEncodedToQuery() {
		Assert::contains(
			'=as23%26key%3Dvalue%3C%3E%22',
			(new Csrf\CsrfLink(
				new Csrf\FakeCsrf('as23&key=value<>"')
			))->protection()
		);
	}
}

(new CsrfLink())->run();
