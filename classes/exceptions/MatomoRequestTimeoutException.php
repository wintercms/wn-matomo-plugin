<?php

namespace Winter\Matomo\Classes\Exceptions;

/**
 * Thrown when a Matomo API request times out or cannot connect.
 */
class MatomoRequestTimeoutException extends MatomoReportingException
{
    public function errorCode(): string
    {
        return 'matomo_request_timeout';
    }

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

    public function isRetryable(): bool
    {
        $connectionError = $this->context()['connection_error'] ?? 'connection_failed';

        // SSL certificate errors are not retryable without configuration changes
        return $connectionError !== 'ssl_certificate';
    }

    public function severity(): string
    {
        return 'warning';
    }
}
