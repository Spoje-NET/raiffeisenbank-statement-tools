<?php

declare(strict_types=1);

/**
 * This file is part of the RaiffeisenBank Statement Tools package
 *
 * https://github.com/Spoje-NET/pohoda-raiffeisenbank
 *
 * (c) Spoje.Net IT s.r.o. <https://spojenet.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet\RaiffeisenBank\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Example test class to demonstrate testing structure.
 *
 * This class should be replaced with actual test classes for the application components.
 */
class ExampleTest extends TestCase
{
    /**
     * Test that basic assertion works.
     */
    public function testBasicAssertion(): void
    {
        $this->assertTrue(true, 'Basic assertion should pass');
    }

    /**
     * Test that environment can be loaded.
     */
    public function testEnvironmentLoading(): void
    {
        $this->assertFileExists(__DIR__.'/../vendor/autoload.php', 'Composer autoload file should exist');
    }
}
