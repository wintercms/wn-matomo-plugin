<?php

namespace Winter\Matomo\Classes\Exceptions;

/**
 * Base exception for reporting-related Matomo failures.
 */
class MatomoReportingException extends \RuntimeException
{
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
        return 'matomo_reporting_error';
    }

    /**
     * Translation key for user-facing messaging.
     */
    public function userMessageKey(): string
    {
        return 'winter.matomo::lang.reportwidgets.visits_summary.errors.reporting';
    }

    /**
     * Indicates whether this error is usually transient and worth retrying.
     */
    public function isRetryable(): bool
    {
        return false;
    }

    /**
     * Severity hint for logs and UI callout style.
     */
    public function severity(): string
    {
        return 'warning';
    }
}
