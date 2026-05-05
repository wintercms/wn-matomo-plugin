<?php

namespace Winter\Matomo\Classes\Traits;

use Throwable;
use Winter\Matomo\Classes\Exceptions\MatomoReportingException;
use Winter\Matomo\Classes\Exceptions\MatomoRequestTimeoutException;

/**
 * Shared behaviour for native Matomo report widgets.
 */
trait ReportWidgetConcerns
{
    /**
     * Converts a typed exception into an actionable user-facing error message.
     */
    protected function resolveUserErrorMessage(Throwable $exception): string
    {
        if ($exception instanceof MatomoRequestTimeoutException) {
            $context = $exception->context();
            $host = $this->extractHostFromExceptionContext($exception);
            $connectionError = (string) ($context['connection_error'] ?? '');

            if ($connectionError === 'dns_resolution' && $host !== null) {
                return (string) trans('winter.matomo::lang.reportwidgets.errors.host_unreachable', [
                    'host' => $host,
                ]);
            }

            if ($connectionError === 'connection_refused' && $host !== null) {
                return (string) trans('winter.matomo::lang.reportwidgets.errors.connection_refused', [
                    'host' => $host,
                ]);
            }
        }

        if ($exception instanceof MatomoReportingException) {
            return (string) trans($exception->userMessageKey());
        }

        return (string) trans('winter.matomo::lang.reportwidgets.errors.unexpected');
    }

    /**
     * Extracts a hostname from typed exception context if available.
     */
    protected function extractHostFromExceptionContext(MatomoReportingException $exception): ?string
    {
        $context = $exception->context();

        $host = $context['host'] ?? null;
        if (is_string($host) && $host !== '') {
            return $host;
        }

        $endpoint = $context['endpoint'] ?? null;
        if (!is_string($endpoint) || $endpoint === '') {
            return null;
        }

        $parsed = parse_url($endpoint, PHP_URL_HOST);

        return (is_string($parsed) && $parsed !== '') ? $parsed : null;
    }

    /**
     * Resolves the translated label for an option value from a lang options array.
     */
    protected function translatedOptionLabel(string $optionsLangKey, string|int $selectedValue): string
    {
        $options = trans($optionsLangKey);
        if (!is_array($options)) {
            return (string) $selectedValue;
        }

        return (string) ($options[$selectedValue] ?? $selectedValue);
    }

    /**
     * Converts a duration in seconds to mm:ss format.
     */
    protected function formatDuration(int $seconds): string
    {
        $minutes = intdiv(max(0, $seconds), 60);
        $remainingSeconds = max(0, $seconds) % 60;

        return sprintf('%02d:%02d', $minutes, $remainingSeconds);
    }

    /**
     * Render the refresh button partial
     *
     * @param array $data Additional data to pass to the partial
     * @return string
     */
    protected function renderRefreshButton(array $data = []): string
    {
        return $this->makePartial('$/winter/matomo/views/partials/_report_refresh_button', $data);
    }
}
