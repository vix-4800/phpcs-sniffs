<?php

declare(strict_types=1);

namespace VixPHPCS\Tests\Sniffs\Attributes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use VixPHPCS\Sniffs\Attributes\ForbiddenAttributesSniff;
use VixPHPCS\Tests\BaseTest;

/**
 * @internal
 */
#[CoversClass(ForbiddenAttributesSniff::class)]
final class ForbiddenAttributesSniffTest extends BaseTest
{
    protected const string SNIFF = 'VixPHPCS.Attributes.ForbiddenAttributes';

    #[Test]
    public function forbiddenAttributeTriggersWarning(): void
    {
        $code = <<<'PHP'
            <?php

            use JetBrains\PhpStorm\ArrayShape;

            class Test
            {
                #[ArrayShape(['id' => 'int'])]
                public function toArray(): array
                {
                    return ['id' => 1];
                }
            }
            PHP;

        $result = $this->runPhpcs($code);
        $this->assertContainsWarning($result, 'Usage of attribute "ArrayShape" is forbidden.');
    }

    #[Test]
    public function fullyQualifiedForbiddenAttributeTriggersWarning(): void
    {
        $code = <<<'PHP'
            <?php

            class Test
            {
                #[\JetBrains\PhpStorm\ArrayShape(['id' => 'int'])]
                public function toArray(): array
                {
                    return ['id' => 1];
                }
            }
            PHP;

        $result = $this->runPhpcs($code);
        $this->assertContainsWarning($result, 'Usage of attribute "\JetBrains\PhpStorm\ArrayShape" is forbidden.');
    }

    #[Test]
    public function allowedAttributeDoesNotTriggerWarning(): void
    {
        $code = <<<'PHP'
            <?php

            class Test
            {
                #[ReturnTypeWillChange]
                public function jsonSerialize()
                {
                    return [];
                }
            }
            PHP;

        $result = $this->runPhpcs($code);
        $this->assertNoViolations($result);
    }

    #[Test]
    public function customForbiddenAttribute(): void
    {
        $sniffPath = __DIR__ . '/../../../src/VixPHPCS/Sniffs/Attributes/ForbiddenAttributesSniff.php';
        $ruleset = <<<XML
            <?xml version="1.0"?>
            <ruleset name="Test">
                <rule ref="{$sniffPath}">
                    <properties>
                        <property name="forbiddenAttributes" type="array">
                            <element value="My\\Custom\\Attribute"/>
                        </property>
                    </properties>
                </rule>
            </ruleset>
            XML;

        $rulesetFile = tempnam(sys_get_temp_dir(), 'phpcs_ruleset_');
        $rulesetPath = $rulesetFile . '.xml';
        rename($rulesetFile, $rulesetPath);
        file_put_contents($rulesetPath, $ruleset);

        $code = <<<'PHP'
            <?php

            #[My\Custom\Attribute]
            class Test {}
            PHP;

        $tempFile = tempnam(sys_get_temp_dir(), 'phpcs_test_');
        file_put_contents($tempFile, $code);

        $phpcsPath = __DIR__ . '/../../../vendor/bin/phpcs';
        $command = sprintf(
            '%s --standard=%s %s 2>&1',
            escapeshellarg($phpcsPath),
            escapeshellarg($rulesetPath),
            escapeshellarg($tempFile),
        );

        $output = shell_exec($command);

        unlink($rulesetPath);
        unlink($tempFile);

        $this->assertContainsWarning($output ?? '', 'Usage of attribute "My\Custom\Attribute" is forbidden.');
    }
}
