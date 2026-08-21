<?php

namespace Winter\Matomo\Classes\Exceptions;

/**
 * Thrown when Matomo returns an unexpected server-side response.
 */
class MatomoServerException extends MatomoReportingException
{
    protected const ERROR_CODE = 'matomo_server_error';
    protected const USER_MESSAGE_KEY = 'winter.matomo::lang.reportwidgets.errors.server';
    protected const IS_RETRYABLE = true;
    protected const SEVERITY = 'error';
}
