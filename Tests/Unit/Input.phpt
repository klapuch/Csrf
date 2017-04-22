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

final class Input extends Tester\TestCase {
	public function testHiddenInput() {
		Assert::contains(
			'<input type="hidden"',
			(new Csrf\Input(new Csrf\FakeProtection('abc123')))->coverage()
		);
	}

	public function testPassedValue() {
		Assert::contains(
			'value="abc123"',
			(new Csrf\Input(new Csrf\FakeProtection('abc123')))->coverage()
		);
	}

	public function testNamedInput() {
		Assert::match(
			'~name="[_\S]+"~',
			(new Csrf\Input(new Csrf\FakeProtection('abc123')))->coverage()
		);
	}

	public function testEnclosingElement() {
		Assert::contains(
			'/>',
			(new Csrf\Input(new Csrf\FakeProtection('abc123')))->coverage()
		);
	}

	public function testProtectionAsValidXml() {
		Assert::noError(
			function() {
				new \SimpleXMLElement(
					(new Csrf\Input(
						new Csrf\FakeProtection('&@\'<>="')
					))->coverage()
				);
			}
		);
	}

	public function testProtectionAsValidHtml() {
		Assert::noError(
			function() {
				$dom = new \DOMDocument();
				$dom->loadHTML(
					(new Csrf\Input(
						new Csrf\FakeProtection('&@\'<>="')
					))->coverage()
				);
			}
		);
	}

	public function testProperlyEncodedAccordingToInput() {
		Assert::contains(
			'&amp;&apos;&lt;&gt;&quot;',
			(new Csrf\Input(
				new Csrf\FakeProtection('&\'<>"')
			))->coverage()
		);
	}
}

(new Input())->run();