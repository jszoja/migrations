<?php

declare(strict_types=1);

namespace Doctrine\Migrations\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use function file_exists;
use function realpath;
use function sprintf;

/**
 * @requires OS Linux|Darwin
 */
class BoxPharCompileTest extends TestCase
{
    public function testCompile() : void
    {
        $boxPharPath = __DIR__ . '/../../../../box.phar';

        if (! file_exists($boxPharPath)) {
            $this->markTestSkipped('Download box with the ./download-box.sh shell script.');
        }

        $boxPharPath = realpath($boxPharPath);

        $compilePharCommand = sprintf('php %s compile -vvv', $boxPharPath);

        $process = new Process($compilePharCommand);
        $process->run();

        $doctrinePharPath = realpath(__DIR__ . '/../../../../build/doctrine-migrations.phar');

        self::assertTrue($process->isSuccessful());
        self::assertTrue(file_exists($doctrinePharPath));

        $runDoctrinePharCommand = sprintf('php %s', $doctrinePharPath);

        $successful = true;

        $process = new Process($runDoctrinePharCommand);

        $process->start(function ($type, $buffer) use (&$output, &$successful) : void {
            if ($type !== 'err') {
                return;
            }

            $successful = false;
        });

        $process->wait();

        self::assertTrue($successful);
        self::assertTrue($process->isSuccessful());
    }
}
