<?php

namespace Winter\Matomo\Classes\Exceptions;

/**
 * Thrown when Matomo credentials are invalid or rejected.
 */
class MatomoAuthenticationException extends MatomoReportingException
{
    protected const ERROR_CODE = 'matomo_authentication_error';
    protected const USER_MESSAGE_KEY = 'winter.matomo::lang.reportwidgets.errors.authentication';
    protected const IS_RETRYABLE = false;
    protected const SEVERITY = 'warning';
}
