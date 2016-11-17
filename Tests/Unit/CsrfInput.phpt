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

final class CsrfInput extends Tester\TestCase {
	public function testHiddenInput() {
		Assert::contains(
			'<input type="hidden"',
			(new Csrf\CsrfInput(new Csrf\FakeCsrf('abc123')))->protection()
		);
	}

	public function testPassedValue() {
		Assert::contains(
			'value="abc123"',
			(new Csrf\CsrfInput(new Csrf\FakeCsrf('abc123')))->protection()
		);
	}

	public function testNamedInput() {
		Assert::match(
			'~name="[_\S]+"~',
			(new Csrf\CsrfInput(new Csrf\FakeCsrf('abc123')))->protection()
		);
	}

	public function testEnclosingElement() {
		Assert::contains(
			'/>',
			(new Csrf\CsrfInput(new Csrf\FakeCsrf('abc123')))->protection()
		);
	}

	public function testProtectionAsValidXml() {
		Assert::noError(function() {
			new \SimpleXMLElement(
				(new Csrf\CsrfInput(
					new Csrf\FakeCsrf('&@\'<>="')
				))->protection()
			);
		});
	}

	public function testProtectionAsValidHtml() {
		Assert::noError(function() {
			$dom = new \DOMDocument();
			$dom->loadHTML(
				(new Csrf\CsrfInput(
					new Csrf\FakeCsrf('&@\'<>="')
				))->protection()
			);
		});
	}

	public function testProperlyEncodedAccordingToInput() {
		Assert::contains(
			'&amp;@&apos;&lt;&gt;=&quot;',
			(new Csrf\CsrfInput(
				new Csrf\FakeCsrf('&@\'<>="')
			))->protection()
		);
	}
}

(new CsrfInput())->run();
