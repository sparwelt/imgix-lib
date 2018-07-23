<?php

namespace Sparwelt\ImgixLib\Components;

use Sparwelt\ImgixLib\Exception\ConfigurationException;
use Sparwelt\ImgixLib\Model\CdnConfiguration;

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
     */
    public static function parseArray(array $arrayConfiguration)
    {
        $parsedCdns = [];

        foreach ($arrayConfiguration as $cdn) {
            self::validateFileds($cdn);

            $parsedCdns[] = new CdnConfiguration(
                $cdn['cdn_domains'],
                isset($cdn['source_domains']) ? $cdn['source_domains'] : [],
                isset($cdn['path_pattern']) ? $cdn['path_pattern'] : null,
                isset($cdn['sign_key']) ? $cdn['sign_key'] : null,
                isset($cdn['shard_strategy']) ? $cdn['shard_strategy'] : 'crc'
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

        if (isset($cdn['path_pattern']) && !is_string($cdn['path_pattern'])) {
            throw new ConfigurationException(
                sprintf('String value expected for "path_pattern" for configuration %s', serialize($cdn))
            );
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
    }
}
