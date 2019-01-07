<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\craft\scorecard\queue;

use Craft;
use craft\helpers\Json;
use craft\queue\BaseJob;
use craft\queue\JobInterface;
use flipbox\craft\scorecard\metrics\SavableMetricInterface;
use flipbox\craft\scorecard\Scorecard;
use Serializable;
use yii\di\Instance;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 *
 * @property int $parentId
 * @property string $class
 * @property float $score
 * @property float $weight
 * @property string $version
 * @property array|null $settings
 * @property \DateTime $dateCalculated
 */
class CalculateAndSaveMetricJob extends BaseJob implements JobInterface, Serializable
{
    /**
     * @var
     */
    public $metric;

    /**
     * @inheritdoc
     * @return bool
     * @throws \yii\base\InvalidConfigException
     */
    public function execute($queue)
    {
        if (null === ($metric = $this->resolveMetric())) {
            Scorecard::error("Unable to resolve metric.");
            return false;
        }

        // Calculate score
        $metric->getScore();

        if (!$metric->save()) {
            Scorecard::warning(sprintf(
                "Unable to save metric: %s",
                Json::encode($metric->toConfig())
            ));

            return false;
        }

        Scorecard::info("Successfully saved metric.");

        return true;
    }

    /**
     * @return SavableMetricInterface|object
     * @throws \yii\base\InvalidConfigException
     */
    protected function resolveMetric()
    {
        if ($this->metric instanceof SavableMetricInterface) {
            return $this->metric;
        }

        $this->metric = Instance::ensure(
            $this->metric,
            SavableMetricInterface::class
        );

        return $this->metric;
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('scorecard', 'Calculate Metric');
    }

    /**
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function serialize()
    {
        return serialize(
            [
                $this->resolveMetric()->toConfig()
            ]
        );
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        list($this->metric) = unserialize($data);
    }
}
