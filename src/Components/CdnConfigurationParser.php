<?php

namespace Sparwelt\ImgixLib\Components;

use Sparwelt\ImgixLib\Exception\ConfigurationException;
use Sparwelt\ImgixLib\Model\CdnConfiguration;
use Sparwelt\ImgixLib\Utils\Utils;

/**
 * @author Federico Infanti <federico.infanti@sparwelt.de>
 *
 * @since  22.07.18 21:09
 */
class CdnConfigurationParser
{
    /**
     * @param array $arrayConfiguration
     *
     * @return CdnConfiguration[]
     *
     * @throws \Sparwelt\ImgixLib\Exception\ConfigurationException
     */
    public static function parseArray(array $arrayConfiguration)
    {
        $parsedCdns = [];

        foreach ($arrayConfiguration as $cdn) {
            self::validateFileds($cdn);

            $parsedCdns[] = new CdnConfiguration(
                $cdn['cdn_domains'],
                isset($cdn['source_domains']) ? $cdn['source_domains'] : [],
                isset($cdn['path_patterns']) ? $cdn['path_patterns'] : [],
                isset($cdn['sign_key']) ? $cdn['sign_key'] : null,
                isset($cdn['shard_strategy']) ? $cdn['shard_strategy'] : 'crc',
                isset($cdn['use_ssl']) ? $cdn['use_ssl'] : true,
                isset($cdn['default_query_params']) ? $cdn['default_query_params'] : [],
                isset($cdn['generate_filter_params']) ? $cdn['generate_filter_params'] : true
            );
        }

        return $parsedCdns;
    }

    /**
     * @param array $cdn
     *
     * @throws \Sparwelt\ImgixLib\Exception\ConfigurationException
     */
    private static function validateFileds($cdn)
    {
        if (!isset($cdn['cdn_domains'])) {
            throw new ConfigurationException(sprintf('Missing "cdn_domains" for configuration %s', serialize($cdn)));
        }

        if (!is_array($cdn['cdn_domains'])) {
            throw new ConfigurationException(
                sprintf('Array value expected for "cdn_domains" for configuration %s', serialize($cdn))
            );
        }

        if (isset($cdn['path_patterns']) && !is_array($cdn['path_patterns'])) {
            throw new ConfigurationException(
                sprintf('Array value expected for "path_patterns" for configuration %s', serialize($cdn))
            );
        }

        if (isset($cdn['path_patterns'])) {
            foreach ($cdn['path_patterns'] as $pattern) {
                if (!self::isValidRegex($pattern)) {
                    throw new ConfigurationException(sprintf('Invalid regex pattern: %s', $pattern));
                }
            }
        }

        if (isset($cdn['sign_key']) && !is_string($cdn['sign_key'])) {
            throw new ConfigurationException(
                sprintf('String value expected for "sign_key" for configuration %s', serialize($cdn))
            );
        }

        if (isset($cdn['shard_strategy']) && (!is_string($cdn['shard_strategy'])
                || !in_array($cdn['shard_strategy'], ['crc', 'cycle'])
            )) {
            throw new ConfigurationException(
                sprintf('Allowed values for "shard_strategy" are "crc" or "cycle" in configuration %s', serialize($cdn))
            );
        }

        if (isset($cdn['default_query_params']) && (!is_array($cdn['default_query_params']))) {
            throw new ConfigurationException(
                sprintf('Array value expected for "default_query_params" are "crc" or "cycle" in configuration %s', serialize($cdn))
            );
        }

        if (isset($cdn['default_query_params']) && Utils::isMatrix($cdn['default_query_params'])) {
            throw new ConfigurationException(
                sprintf('Monodimensional array expected, matrix given as "default_query_parameters" in configuration: %s', serialize($cdn))
            );
        }

        if (isset($cdn['generate_filter_params']) && !is_bool($cdn['generate_filter_params'])) {
            throw new ConfigurationException(
                sprintf('Boolean expected for "generate_filter_params" in configuration: %s', serialize($cdn))
            );
        }
    }

    /**
     * @param string $pattern
     *
     * @return bool
     */
    private static function isValidRegex($pattern)
    {
        try {
            return preg_match(sprintf('~%s~', $pattern), null) !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
