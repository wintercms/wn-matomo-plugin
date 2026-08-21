<?php

namespace Winter\Matomo\Classes\Exceptions;

/**
 * Base exception for reporting-related Matomo failures.
 */
class MatomoReportingException extends \RuntimeException
{
    /**
     * Protected constants for error classification.
     * Child classes override these constants to customize behavior.
     */
    protected const ERROR_CODE = 'matomo_reporting_error';
    protected const USER_MESSAGE_KEY = 'winter.matomo::lang.reportwidgets.errors.reporting';
    protected const IS_RETRYABLE = false;
    protected const SEVERITY = 'warning';

    /**
     * Safe context values intended for logs and diagnostics.
     *
     * @var array<string, mixed>
     */
    protected array $safeContext;

    /**
     * @param array<string, mixed> $safeContext
     */
    public function __construct(
        string $message = '',
        array $safeContext = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->safeContext = $safeContext;
    }

    /**
     * Returns safe context for structured logging.
     *
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->safeContext;
    }

    /**
     * Stable machine-readable error code.
     */
    public function errorCode(): string
    {
        return static::ERROR_CODE;
    }

    /**
     * Translation key for user-facing messaging.
     */
    public function userMessageKey(): string
    {
        return static::USER_MESSAGE_KEY;
    }

    /**
     * Indicates whether this error is usually transient and worth retrying.
     */
    public function isRetryable(): bool
    {
        return static::IS_RETRYABLE;
    }

    /**
     * Severity hint for logs and UI callout style.
     */
    public function severity(): string
    {
        return static::SEVERITY;
    }
}
