<?php

namespace Psr\Log\Test;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Used for testing purposes.
 *
 * It records all records and gives you access to them for verification.
 */
class TestLogger implements LoggerInterface
{
    use LoggerTrait;

    public array $records = [];
    public array $recordsByLevel = [];

    /**
     * @inheritdoc
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $record = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        $this->recordsByLevel[$record['level']][] = $record;
        $this->records[] = $record;
    }

    /**
     * @param string $level
     * @return bool
     */
    public function hasRecords($level)
    {
        return isset($this->recordsByLevel[$level]);
    }

    /**
     * @param array $record
     * @param string $level
     * @return bool
     */
    public function hasRecord($record, $level)
    {
        if (is_string($record)) {
            $record = ['message' => $record];
        }

        return $this->hasRecordThatPasses(function ($rec) use ($record) {
            if ($rec['message'] !== $record['message']) {
                return false;
            }
            if (isset($record['context']) && $rec['context'] !== $record['context']) {
                return false;
            }
            return true;
        }, $level);
    }

    /**
     * @param string $message
     * @param string $level
     * @return bool
     */
    public function hasRecordThatContains($message, $level)
    {
        return $this->hasRecordThatPasses(fn ($rec) => str_contains($rec['message'], $message), $level);
    }

    /**
     * @param string $regex
     * @param string $level
     * @return bool
     */
    public function hasRecordThatMatches($regex, $level)
    {
        return $this->hasRecordThatPasses(fn ($rec) => preg_match($regex, $rec['message']) > 0, $level);
    }

    /**
     * @param callable $predicate
     * @param string $level
     * @return bool
     */
    public function hasRecordThatPasses(callable $predicate, $level)
    {
        if (!isset($this->recordsByLevel[$level])) {
            return false;
        }
        foreach ($this->recordsByLevel[$level] as $i => $rec) {
            if ($predicate($rec, $i)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @deprecated Since psr/log-util 1.1
     */
    public function __call($method, $args)
    {
        @trigger_error(sprintf('Since psr/log-util 1.1: Method "%s" is deprecated and should not be called. Use method "%s" instead.', __FUNCTION__, $method), \E_USER_DEPRECATED);

        if (preg_match('/(.*)(Debug|Info|Notice|Warning|Error|Critical|Alert|Emergency)(.*)/', $method, $matches) > 0) {
            $genericMethod = $matches[1] . ('Records' !== $matches[3] ? 'Record' : '') . $matches[3];
            $level = strtolower($matches[2]);
            if (method_exists($this, $genericMethod)) {
                $args[] = $level;
                return call_user_func_array([$this, $genericMethod], $args);
            }
        }
        throw new \BadMethodCallException('Call to undefined method ' . get_class($this) . '::' . $method . '()');
    }

    public function hasEmergency($record): bool
    {
        return $this->hasRecord($record, 'emergency');
    }

    public function hasAlert($record): bool
    {
        return $this->hasRecord($record, 'alert');
    }

    public function hasCritical($record): bool
    {
        return $this->hasRecord($record, 'critical');
    }

    public function hasError($record): bool
    {
        return $this->hasRecord($record, 'error');
    }

    public function hasWarning($record): bool
    {
        return $this->hasRecord($record, 'warning');
    }

    public function hasNotice($record): bool
    {
        return $this->hasRecord($record, 'notice');
    }

    public function hasInfo($record): bool
    {
        return $this->hasRecord($record, 'info');
    }

    public function hasDebug($record): bool
    {
        return $this->hasRecord($record, 'debug');
    }

    public function hasEmergencyRecords(): bool
    {
        return $this->hasRecords('emergency');
    }

    public function hasAlertRecords(): bool
    {
        return $this->hasRecords('alert');
    }

    public function hasCriticalRecords(): bool
    {
        return $this->hasRecords('critical');
    }

    public function hasErrorRecords(): bool
    {
        return $this->hasRecords('error');
    }

    public function hasWarningRecords(): bool
    {
        return $this->hasRecords('warning');
    }

    public function hasNoticeRecords(): bool
    {
        return $this->hasRecords('notice');
    }

    public function hasInfoRecords(): bool
    {
        return $this->hasRecords('info');
    }

    public function hasDebugRecords(): bool
    {
        return $this->hasRecords('debug');
    }

    public function hasEmergencyThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'emergency');
    }

    public function hasAlertThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'alert');
    }

    public function hasCriticalThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'critical');
    }

    public function hasErrorThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'error');
    }

    public function hasWarningThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'warning');
    }

    public function hasNoticeThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'notice');
    }

    public function hasInfoThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'info');
    }

    public function hasDebugThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'debug');
    }

    public function hasEmergencyThatMatches(string $regex): bool
    {
        return $this->hasRecordThatMatches($regex, 'emergency');
    }

    public function hasAlertThatMatches(string $regex): bool
    {
        return $this->hasRecordThatMatches($regex, 'alert');
    }

    public function hasCriticalThatMatches(string $regex): bool
    {
        return $this->hasRecordThatMatches($regex, 'critical');
    }

    public function hasErrorThatMatches(string $regex): bool
    {
        return $this->hasRecordThatMatches($regex, 'error');
    }

    public function hasWarningThatMatches(string $regex): bool
    {
        return $this->hasRecordThatMatches($regex, 'warning');
    }

    public function hasNoticeThatMatches(string $regex): bool
    {
        return $this->hasRecordThatMatches($regex, 'notice');
    }

    public function hasInfoThatMatches(string $regex): bool
    {
        return $this->hasRecordThatMatches($regex, 'info');
    }

    public function hasDebugThatMatches(string $regex): bool
    {
        return $this->hasRecordThatMatches($regex, 'debug');
    }

    public function hasEmergencyThatPasses(callable $predicate): bool
    {
        return $this->hasRecordThatPasses($predicate, 'emergency');
    }

    public function hasAlertThatPasses(callable $predicate): bool
    {
        return $this->hasRecordThatPasses($predicate, 'alert');
    }

    public function hasCriticalThatPasses(callable $predicate): bool
    {
        return $this->hasRecordThatPasses($predicate, 'critical');
    }

    public function hasErrorThatPasses(callable $predicate): bool
    {
        return $this->hasRecordThatPasses($predicate, 'error');
    }

    public function hasWarningThatPasses(callable $predicate): bool
    {
        return $this->hasRecordThatPasses($predicate, 'warning');
    }

    public function hasNoticeThatPasses(callable $predicate): bool
    {
        return $this->hasRecordThatPasses($predicate, 'notice');
    }

    public function hasInfoThatPasses(callable $predicate): bool
    {
        return $this->hasRecordThatPasses($predicate, 'info');
    }

    public function hasDebugThatPasses(callable $predicate): bool
    {
        return $this->hasRecordThatPasses($predicate, 'debug');
    }

    public function reset()
    {
        $this->records = [];
        $this->recordsByLevel = [];
    }
}
