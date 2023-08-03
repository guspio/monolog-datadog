<?php declare(strict_types=1);

namespace MonologDatadog\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

use Exception;

use Monolog\Handler\Curl\Util;

use Monolog\Formatter\JsonFormatter;

/**
 * Sends logs to Datadog Logs using Curl integrations
 *
 * You'll need a Datzdog account to use this handler.
 *
 * @see https://docs.datadoghq.com/logs/ Datadog Logs Documentation
 * @author Gusp <contact@gusp.io>
 */
class DatadogHandler extends AbstractProcessingHandler
{
    /**
     * Datadog Api Key access
     *
     * @var string
     */
    protected const DATADOG_LOG_HOST = 'https://http-intake.logs.datadoghq.com';

    /**
     * Datadog Api Key access
     *
     * @var string
     */
    private $apiKey;

    /**
     * Datadog optionals attributes
     *
     * @var array
     */
    private $attributes;

    /**
     * @param string     $apiKey     Datadog Api Key access
     * @param array      $attributes Some options fore Datadog Logs
     * @param string|int $level      The minimum logging level at which this handler will be triggered
     * @param bool       $bubble     Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(
        string $apiKey,
        array $attributes = [],
        $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        if (!extension_loaded('curl')) {
            throw new MissingExtensionException('The curl extension is needed to use the DatadogHandler');
        }

        parent::__construct($level, $bubble);

        $this->apiKey = $this->getApiKey($apiKey);
        $this->attributes = $attributes;
    }

    /**
     * Handles a log record
     */
    protected function write(array $record): void
    {
        $this->send($record['formatted']);
    }

    /**
     * Send request to @link https://http-intake.logs.datadoghq.com on send action.
     * @param string $record
     */
    protected function send(string $record)
    {
        $headers = ['Content-Type:application/json'];

        $source = $this->getSource();
        $hostname = $this->getHostname();
        $service = $this->getService($record);

        $url = self::DATADOG_LOG_HOST . '/v1/input/';
        $url .= $this->apiKey;
        $url .= '?ddsource=' . $source . '&service=' . $service . '&hostname=' . $hostname;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $record);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        Util::execute($ch);
    }

    /**
     * Get Datadog Api Key from $attributes params.
     * @param string $apiKey
     */
    protected function getApiKey($apiKey)
    {
        if ($apiKey) {
            return $apiKey;
        } else {
            throw new Exception('The Datadog Api Key is required');
        }
    }

    /**
     * Get Datadog Source from $attributes params.
     * @param string $apiKey
     */
    protected function getSource()
    {
        return !empty($this->attributes['source']) ? $this->attributes['source'] : 'php';
    }

    /**
     * Get Datadog Service from $attributes params.
     * @param string $apiKey
     */
    protected function getService($record)
    {
        $channel = json_decode($record, true);

        return !empty($this->attributes['service']) ? $this->attributes['service'] : $channel['channel'];
    }

    /**
     * Get Datadog Hostname from $attributes params.
     * @param string $apiKey
     */
    protected function getHostname()
    {
        return !empty($this->attributes['hostname']) ? $this->attributes['hostname'] : $_SERVER['SERVER_NAME'];
    }

    /**
     * Returns the default formatter to use with this handler
     *
     * @return JsonFormatter
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new JsonFormatter();
    }
}