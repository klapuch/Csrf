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

final class Link extends Tester\TestCase {
	public function testProtectionAsPartOfQuery() {
		Assert::contains(
			'=abc123',
			(new Csrf\Link(
				new Csrf\FakeProtection('abc123')
			))->coverage()
		);
	}

	public function testProtectionWithKeyValue() {
		Assert::match(
			'~^_[\S]+=[\w]+$~',
			(new Csrf\Link(
				new Csrf\FakeProtection('abc')
			))->coverage()
		);
	}

	public function testProtectionEncodedToQuery() {
		Assert::contains(
			'=as23%26key%3Dvalue%3C%3E%22',
			(new Csrf\Link(
				new Csrf\FakeProtection('as23&key=value<>"')
			))->coverage()
		);
	}
}

(new Link())->run();
