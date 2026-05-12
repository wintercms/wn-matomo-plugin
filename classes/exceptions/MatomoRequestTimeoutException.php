<?php

namespace Winter\Matomo\Classes\Exceptions;

/**
 * Thrown when a Matomo API request times out or cannot connect.
 */
class MatomoRequestTimeoutException extends MatomoReportingException
{
    protected const ERROR_CODE = 'matomo_request_timeout';
    protected const SEVERITY = 'warning';

    /**
     * Override to provide context-aware message key based on connection error type.
     */
    public function userMessageKey(): string
    {
        $connectionError = $this->context()['connection_error'] ?? 'connection_failed';

        return match ($connectionError) {
            'dns_resolution'    => 'winter.matomo::lang.reportwidgets.errors.dns_resolution',
            'connection_refused' => 'winter.matomo::lang.reportwidgets.errors.connection_refused',
            'ssl_certificate'   => 'winter.matomo::lang.reportwidgets.errors.ssl_certificate',
            'timeout'           => 'winter.matomo::lang.reportwidgets.errors.timeout',
            default             => 'winter.matomo::lang.reportwidgets.errors.connection_failed',
        };
    }

    /**
     * Override to provide context-aware retry logic based on connection error type.
     */
    public function isRetryable(): bool
    {
        $connectionError = $this->context()['connection_error'] ?? 'connection_failed';

        // SSL certificate errors are not retryable without configuration changes
        return $connectionError !== 'ssl_certificate';
    }
}
