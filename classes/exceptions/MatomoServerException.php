<?php

namespace Winter\Matomo\Classes\Exceptions;

/**
 * Thrown when Matomo returns an unexpected server-side response.
 */
class MatomoServerException extends MatomoReportingException
{
    public function errorCode(): string
    {
        return 'matomo_server_error';
    }

    public function userMessageKey(): string
    {
        return 'winter.matomo::lang.reportwidgets.errors.server';
    }

    public function isRetryable(): bool
    {
        return true;
    }

    public function severity(): string
    {
        return 'error';
    }
}
