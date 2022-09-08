<?php

namespace Fig\Log\Tests\Test;

use Psr\Log\Test\LoggerInterfaceTest;
use Psr\Log\Test\TestLogger;

/**
 * Test classes from psr/log 1.1.x
 *
 * @covers \Psr\Log\Test\TestLogger
 * @covers \Psr\Log\Test\LoggerInterfaceTest
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 */
class TestLoggerTest extends LoggerInterfaceTest
{
    private TestLogger $logger;

    public function getLogger(): TestLogger
    {
        if (!isset($this->logger)) {
            $this->logger = new TestLogger();
        }

        return $this->logger;
    }

    public function getLogs(): array
    {
        $logs = array();
        foreach ($this->getLogger()->records as $record) {
            // Interpolate context
            $message = preg_replace_callback(
                '!\{([^\}\s]*)\}!',
                fn (array $matches): string => $record['context'][$matches[1] ?? null] ?? $matches[0],
                $record['message']
            );
            $logs[] = $record['level'] . ' ' . $message;
        }

        return $logs;
    }

    public function testThrowsOnInvalidLevel(): void
    {
        self::markTestSkipped('TestLogger from psr/log 1.1 does not throw on invalid level.');
    }

    /**
     * @dataProvider getLogLevels
     */
    public function testHasRecord(string $level): void
    {
        $levelMethod = 'has'.ucfirst($level);
        $logger = $this->getLogger();

        $this->assertFalse(call_user_func([$logger, $levelMethod.'Records']));

        $logger->log($level, $level.' Message', ['foo' => 'bar']);
        $logger->log($level, $level.' Hello');

        $this->assertTrue(call_user_func([$logger, $levelMethod.'Records']));

        $record = $level.' Message';
        $this->assertTrue($logger->hasRecord($record, $level), 'hasRecord without context');
        $this->assertTrue(call_user_func([$logger, $levelMethod], $record), $levelMethod.' without context');

        $record = ['message' => $level.' Message'];
        $this->assertTrue($logger->hasRecord($record, $level), 'hasRecord without context');
        $this->assertTrue(call_user_func([$logger, $levelMethod], $record), $levelMethod.' without context');

        $record = ['message' => $level.' Message', ['foo' => 'bar']];
        $this->assertTrue($logger->hasRecord($record, $level), 'hasRecord with context');
        $this->assertTrue(call_user_func([$logger, $levelMethod], $record), $levelMethod.' with context');

        $this->assertTrue(call_user_func([$logger, $levelMethod.'ThatContains'], 'Message'), $levelMethod.'ThatContains');
        $this->assertTrue(call_user_func([$logger, $levelMethod.'ThatMatches'], '/^[a-z]+ Message$/i'), $levelMethod.'ThatMatches');
        $this->assertTrue(call_user_func([$logger, $levelMethod.'ThatPasses'], fn (array $record) => $record['message'] === $level.' Message'), $levelMethod.'ThatMatches');
    }

    public function getLogLevels(): array
    {
        return [
            ['debug'],
            ['info'],
            ['notice'],
            ['warning'],
            ['error'],
            ['critical'],
            ['alert'],
            ['emergency'],
        ];
    }

    public function testFalseHasRecord(): void
    {
        $logger = $this->getLogger();
        $logger->debug('debug message');
        $logger->debug('log message');
        $logger->debug('log message with context', ['debug' => true]);
        $logger->debug('log message with context', ['debug' => false]);
        $logger->warning('log message');
        $logger->warning('warning message');

        $this->assertFalse($logger->hasAlert('my message'));
        $this->assertFalse($logger->hasAlert(['message' => 'my message']));
        $this->assertFalse($logger->hasAlertRecords());
        $this->assertFalse($logger->hasAlertThatContains('my message'));
        $this->assertFalse($logger->hasAlertThatMatches('/my message/'));
        $this->assertFalse($logger->hasAlertThatPasses(fn (array $record) => true));

        $this->assertFalse($logger->hasDebug('warning message'));
        $this->assertFalse($logger->hasDebug(['message' => 'warning message']));
        $this->assertFalse($logger->hasDebugThatContains('warning message'));
        $this->assertFalse($logger->hasDebugThatMatches('/warning message/'));
        $this->assertFalse($logger->hasDebugThatPasses(fn (array $record) => 'warning message' === $record['message']));
    }
}
