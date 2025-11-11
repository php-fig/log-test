<?php

namespace Psr\Log\Test;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

/**
 * Used for testing purposes.
 *
 * It records all records and gives you access to them for verification.
 *
 * @psalm-type log_record_array array{level: string|int, message: string|\Stringable, context: mixed[]}
 * @psalm-type has_record_array array{level?: mixed, message?: string|\Stringable, context?: array<array-key, mixed>}
 */
class TestLogger implements LoggerInterface
{
    use LoggerTrait;

    /** @var list<log_record_array> */
    public array $records = [];
    /** @var array<array-key, list<log_record_array>> */
    public array $recordsByLevel = [];

    private bool $placeholderInterpolation;

    public function __construct(bool $placeholderInterpolation = false)
    {
        $this->placeholderInterpolation = $placeholderInterpolation;
    }

    /**
     * @inheritdoc
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        if ($this->placeholderInterpolation === true) {
            $message = $this->interpolate($message, $context);
        }

        $level = $this->normalizeLevel($level);

        $record = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        $this->recordsByLevel[$record['level']][] = $record;
        $this->records[] = $record;
    }

    /**
     * @param mixed $level
     * @return bool
     */
    public function hasRecords($level)
    {
        return isset($this->recordsByLevel[$this->normalizeLevel($level)]);
    }

    /**
     * @param has_record_array|string $record
     * @param mixed $level
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
        }, $this->normalizeLevel($level));
    }

    /**
     * @param string $message
     * @param mixed $level
     * @return bool
     */
    public function hasRecordThatContains($message, $level)
    {
        return $this->hasRecordThatPasses(fn ($rec) => str_contains($rec['message'], $message), $this->normalizeLevel($level));
    }

    /**
     * @param string $regex
     * @param mixed $level
     * @return bool
     */
    public function hasRecordThatMatches($regex, $level)
    {
        return $this->hasRecordThatPasses(fn ($rec) => preg_match($regex, $rec['message']) > 0, $this->normalizeLevel($level));
    }

    /**
     * @param callable $predicate
     * @param mixed $level
     * @return bool
     */
    public function hasRecordThatPasses(callable $predicate, $level)
    {
        $level = $this->normalizeLevel($level);

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

    /**
     * @param has_record_array|string $record
     */
    public function hasEmergency($record): bool
    {
        return $this->hasRecord($record, 'emergency');
    }

    /**
     * @param has_record_array|string $record
     */
    public function hasAlert($record): bool
    {
        return $this->hasRecord($record, 'alert');
    }

    /**
     * @param has_record_array|string $record
     */
    public function hasCritical($record): bool
    {
        return $this->hasRecord($record, 'critical');
    }

    /**
     * @param has_record_array|string $record
     */
    public function hasError($record): bool
    {
        return $this->hasRecord($record, 'error');
    }

    /**
     * @param has_record_array|string $record
     */
    public function hasWarning($record): bool
    {
        return $this->hasRecord($record, 'warning');
    }

    /**
     * @param has_record_array|string $record
     */
    public function hasNotice($record): bool
    {
        return $this->hasRecord($record, 'notice');
    }

    /**
     * @param has_record_array|string $record
     */
    public function hasInfo($record): bool
    {
        return $this->hasRecord($record, 'info');
    }

    /**
     * @param has_record_array|string $record
     */
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

    /**
     * @param string $message
     * @return bool
     */
    public function hasEmergencyThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'emergency');
    }

    /**
     * @param string $message
     * @return bool
     */
    public function hasAlertThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'alert');
    }

    /**
     * @param string $message
     * @return bool
     */
    public function hasCriticalThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'critical');
    }

    /**
     * @param string $message
     * @return bool
     */
    public function hasErrorThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'error');
    }

    /**
     * @param string $message
     * @return bool
     */
    public function hasWarningThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'warning');
    }

    /**
     * @param string $message
     * @return bool
     */
    public function hasNoticeThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'notice');
    }

    /**
     * @param string $message
     * @return bool
     */
    public function hasInfoThatContains($message): bool
    {
        return $this->hasRecordThatContains($message, 'info');
    }

    /**
     * @param string $message
     * @return bool
     */
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

    /**
     * @return void
     */
    public function reset()
    {
        $this->records = [];
        $this->recordsByLevel = [];
    }

    /**
     * Interpolates context values into the message placeholders.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return string
     */
    private function interpolate($message, array $context = []): string
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            // check that the value can be cast to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    /**
     * @param mixed $level
     */
    protected static function normalizeLevel($level): int|string
    {
        if ($level instanceof \UnitEnum) {
            $level = $level->value;
        }

        if ($level instanceof \Stringable) {
            $level = (string) $level;
        }
        
        if (is_string($level) || is_int($level)) {
            return $level;
        }

        throw new \InvalidArgumentException('The given level of type "'.gettype($level).'" could not be normalized to a string or int.');
    }
}
