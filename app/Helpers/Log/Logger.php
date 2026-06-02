<?php

declare(strict_types=1);

/**
 * Catalyst PHP Framework
 *
 * A modern PHP 8.4 framework for building
 * robust and scalable web applications.
 *
 * PHP Version 8.4 (Required).
 *
 * @package    Catalyst
 *
 * @author     Walter Nuñez (arcanisgk/original founder)
 * @email      <wnunez@lh-2.net>
 * @email      <icarosnet@gmail.com>
 * @copyright  2024-2026 Walter Francisco Nuñez Cruz and Icaros Net
 * @license    Proprietary - https://catalyst.lh-2.net/license
 *
 * @version    GIT: See repository tags
 *
 * @category   Framework
 * @filesource
 *
 * @link       https://catalyst.lh-2.net Project homepage
 * @see        https://catalyst.lh-2.net/docs Documentation
 *
 */

namespace Catalyst\Helpers\Log;

use Catalyst\Framework\Traits\SingletonTrait;
use Exception;

/**
 * Logger class for recording system events, errors, and user activities
 *
 * @package Catalyst\Helpers\Log;
 * Responsibility: Filters, sanitizes, formats and writes application log events.
 */
class Logger
{
    use SingletonTrait;

    /**
     * @var bool
     */
    private static bool $hasBeenInitialized = false;

    private LoggerSettings $settings;

    private LoggerConfigurator $configurator;

    private LoggerContextSanitizer $contextSanitizer;

    private LoggerRequestClassifier $requestClassifier;

    private LoggerEntryFormatter $entryFormatter;

    private LoggerWriter $writer;

    private LoggerInlineDisplay $inlineDisplay;

    /**
     * @var string
     */
    private string $requestId;

    /**
     * Initializes logger settings, directory readiness and formatting collaborators.
     *
     * Responsibility: Prepares the singleton logger pipeline used by channel writers and context sanitization.
     */
    protected function __construct()
    {
        $this->settings = new LoggerSettings(
            LOG_DIR,
            IS_DEVELOPMENT ? LoggerLevelMap::PRIORITIES['DEBUG'] : LoggerLevelMap::PRIORITIES['ERROR']
        );
        $this->configurator = new LoggerConfigurator();
        $this->configurator->ensureLogDirectory($this->settings->logDirectory);
        $this->contextSanitizer = new LoggerContextSanitizer();
        $this->requestClassifier = new LoggerRequestClassifier();
        $this->entryFormatter = new LoggerEntryFormatter();
        $this->writer = new LoggerWriter();
        $this->inlineDisplay = new LoggerInlineDisplay();
        $this->requestId = uniqid('req-', true);
    }

    /**
     * Configure logger settings - will only run once per request.
     *
     * Responsibility: Configure logger settings - will only run once per request.
     * @param array $config Configuration options
     * @return self For method chaining
     */
    public function configure(array $config): self
    {
        $this->configurator->applyRuntimeOptions($this->settings, $config);

        if (!self::$hasBeenInitialized) {
            $this->configurator->applyInitialOptions($this->settings, $config);
            self::$hasBeenInitialized = true;
        }

        return $this;
    }

    /**
     * Log a message with a specific level.
     *
     * Responsibility: Log a message with a specific level.
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context data
     * @return void Success status
     * @throws Exception
     */
    public function log(string $level, string $message, array $context = []): void
    {
        $level = LoggerLevelMap::normalize($level);
        if ($level === null) {
            return;
        }

        $levelPriority = LoggerLevelMap::priority($level);
        if ($levelPriority === null || $levelPriority > $this->settings->minimumLogLevel) {
            return;
        }

        if (!$this->shouldLogWebAssetRequest($level)) {
            return;
        }

        $context = $this->contextSanitizer->sanitize($context);
        $logEntry = $this->entryFormatter->format($level, $message, $context, $this->getRequestId());

        $this->writer->write($this->settings, $level, $logEntry);
        $this->displayLog($level, $logEntry);
    }

    /**
     * Determines whether the current asset request should emit the given level.
     *
     * Responsibility: Determines whether the current asset request should emit the given level.
     */
    private function shouldLogWebAssetRequest(string $level): bool
    {
        if (IS_CLI) {
            return true;
        }

        if ($this->requestClassifier->classify() !== 'asset') {
            return true;
        }

        if ($level === 'ERROR' && !$this->settings->logAssetErrors) {
            return false;
        }

        return !($level === 'INFO' && LoggerLevelMap::PRIORITIES['DEBUG'] > $this->settings->minimumLogLevel);
    }

    /**
     * Get a unique ID for this request.
     *
     * Responsibility: Get a unique ID for this request.
     * @return string Request ID
     */
    private function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * Logs a mail-related event through the standard info channel.
     *
     * Responsibility: Logs a mail-related event through the standard info channel.
     * @param string $event
     * @param string $message
     * @param array $context
     * @return void
     * @throws Exception
     */
    public function mail(string $event, string $message, array $context): void
    {
        $context['event_type'] = 'user';
        $context['event_name'] = $event;
        $this->info($message, $context);
    }

    /**
     * Display log in the terminal or browser - will only be used if explicitly enabled.
     *
     * Responsibility: Display log in the terminal or browser - will only be used if explicitly enabled.
     * @param string $level Log level
     * @param string $logEntry Formatted log entry
     * @return void
     * @throws Exception
     */
    private function displayLog(string $level, string $logEntry): void
    {
        if (!$this->settings->displayLogs) {
            return;
        }

        if (!IS_CLI) {
            return;
        }

        $this->inlineDisplay->render($level, $logEntry);
    }

    /**
     * Log an emergency message.
     *
     * Responsibility: Log an emergency message.
     * @param string $message MailMessage to log
     * @param array $context Additional context
     * @return void Success status
     * @throws Exception
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }

    /**
     * Log an alert message.
     *
     * Responsibility: Log an alert message.
     * @param string $message MailMessage to log
     * @param array $context Additional context
     * @return void Success status
     * @throws Exception
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }

    /**
     * Log a critical message.
     *
     * Responsibility: Log a critical message.
     * @param string $message MailMessage to log
     * @param array $context Additional context
     * @return void Success status
     * @throws Exception
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    /**
     * Log an error message.
     *
     * Responsibility: Log an error message.
     * @param string $message MailMessage to log
     * @param array $context Additional context
     * @return void Success status
     * @throws Exception
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    /**
     * Log a warning message.
     *
     * Responsibility: Log a warning message.
     * @param string $message MailMessage to log
     * @param array $context Additional context
     * @return void Success status
     * @throws Exception
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    /**
     * Log a notice message.
     *
     * Responsibility: Log a notice message.
     * @param string $message MailMessage to log
     * @param array $context Additional context
     * @return void Success status
     * @throws Exception
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }

    /**
     * Log an info message.
     *
     * Responsibility: Log an info message.
     * @param string $message MailMessage to log
     * @param array $context Additional context
     * @return void Success status
     * @throws Exception
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    /**
     * Log a debug message.
     *
     * Responsibility: Log a debug message.
     * @param string $message MailMessage to log
     * @param array $context Additional context
     * @return void Success status
     * @throws Exception
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    /**
     * Records a system event through the standard informational log channel.
     *
     * Responsibility: Adds system event metadata before delegating sanitized context to the shared logger pipeline.
     *
     * @param string $event Event name
     * @param string $message Event description
     * @param array $context Additional context
     * @return void Success status
     * @throws Exception
     */
    public function system(string $event, string $message, array $context = []): void
    {
        $context['event_type'] = 'system';
        $context['event_name'] = $event;
        $this->info($message, $context);
    }

    /**
     * Records a mail delivery event in the dedicated email log stream.
     *
     * Responsibility: Sanitizes email context and writes a formatted delivery entry through the logger writer.
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param array $context Additional context
     * @return bool Success status
     */
    public function email(string $to, string $subject, array $context = []): bool
    {
        $message = "Email sent to: $to, Subject: $subject";
        $context = $this->contextSanitizer->sanitize($context);
        $logEntry = $this->entryFormatter->formatEmail($message, $context);

        try {
            $this->writer->writeEmail($this->settings, $logEntry);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Records an application user event through the standard informational log channel.
     *
     * Responsibility: Adds user event metadata before delegating sanitized context to the shared logger pipeline.
     *
     * @param string $event Event name
     * @param string $message Event description
     * @param array $context Additional context
     * @return void Success status
     * @throws Exception
     */
    public function user(string $event, string $message, array $context = []): void
    {
        $context['event_type'] = 'user';
        $context['event_name'] = $event;
        $this->info($message, $context);
    }
}
