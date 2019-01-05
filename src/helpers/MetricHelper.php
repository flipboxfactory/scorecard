<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\scorecard\helpers;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use flipbox\ember\helpers\ObjectHelper;
use flipbox\scorecard\metrics\MetricInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class MetricHelper
{

    /*******************************************
     * CREATE
     *******************************************/

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param mixed $config
     * @return MetricInterface
     */
    public static function create($config = []): MetricInterface
    {
        // Force array
        if (!is_array($config)) {
            $config = ArrayHelper::toArray($config, [], false);
        }

        // Extract settings
        $settings = static::resolveSettings(
            ArrayHelper::remove($config, 'settings', [])
        );

        // Merge settings + config
        $config = array_merge($config, $settings);

        /** @noinspection PhpUnhandledExceptionInspection */
        $class = ObjectHelper::checkConfig($config, MetricInterface::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(
            array_merge(
                ['class' => $class],
                $config
            )
        );
    }

    /**
     * @param $settings
     * @return array
     */
    public static function resolveSettings($settings): array
    {
        if (null === $settings) {
            return [];
        }

        if (is_string($settings)) {
            $settings = Json::decodeIfJson($settings);
        }

        if (!is_array($settings)) {
            $settings = ArrayHelper::toArray($settings, [], true);
        }

        return $settings;
    }
}
