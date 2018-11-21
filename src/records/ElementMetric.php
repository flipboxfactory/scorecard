<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\scorecard\records;

use Craft;
use craft\helpers\StringHelper;
use flipbox\ember\helpers\ArrayHelper;
use flipbox\ember\helpers\ModelHelper;
use flipbox\ember\records\ActiveRecordWithId;
use flipbox\ember\records\traits\ElementAttribute;
use flipbox\scorecard\db\ElementMetricQuery;
use flipbox\scorecard\helpers\MetricHelper;
use flipbox\scorecard\metrics\MetricInterface;
use flipbox\scorecard\validators\ElementMetricValidator;

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
 * @property ElementMetric[] $children
 */
abstract class ElementMetric extends ActiveRecordWithId implements MetricInterface
{
    use ElementAttribute;

    /**
     * The default score weight
     */
    const WEIGHT = 1;

    /**
     * The default metric version
     */
    const VERSION = '1.0';

    /**
     * @return array
     */
    const METRICS = [];

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'scorecard_element_metrics';

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['elementId', 'score'];

    /**
     * @var MetricInterface[]
     */
    private $metrics;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->setAttribute(
            'settings',
            MetricHelper::resolveSettings(
                $this->getAttribute('settings')
            )
        );

        // Always this class
        $this->class = static::class;

        // Defaults
        if ($this->getIsNewRecord()) {
            $this->weight = $this->weight ?: static::WEIGHT;
            $this->version = $this->version ?: static::VERSION;
        }
    }

    /**
     * @inheritdoc
     */
    public static function find()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(ElementMetricQuery::class, [get_called_class()]);
    }

    /**
     * @inheritdoc
     */
    public static function instantiate($row)
    {
        $class = $row['class'] ?? static::class;
        return new $class;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            $this->elementRules(),
            [
                [
                    [
                        'class'
                    ],
                    ElementMetricValidator::class
                ],
                [
                    [
                        'parentId',
                    ],
                    'number',
                    'integerOnly' => true
                ],
                [
                    [
                        'score',
                        'weight',
                    ],
                    'number'
                ],
                [
                    [
                        'elementId',
                        'class',
                        'weight',
                        'version',
                    ],
                    'required'
                ],
                [
                    [
                        'class',
                        'settings',
                        'score',
                        'weight',
                        'version'
                    ],
                    'safe',
                    'on' => [
                        ModelHelper::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }


    /*******************************************
     * METRIC INTERFACE
     *******************************************/

    /**
     * @return float
     */
    protected function calculateScore(): float
    {
        // Sum of total/weight
        $total = $weights = 0;

        foreach ($this->getMetrics() as $metric) {
            $total += $metric->getScore();
            $weights += $metric->getWeight();
        }

        return (float)($total / $weights);
    }
    
    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public static function displayName(): string
    {
        return StringHelper::titleize(
            (new \ReflectionClass(static::class))
                ->getShortName()
        );
    }

    /**
     * @inheritdoc
     */
    public function getWeight(): float
    {
        return (float)$this->getAttribute('weight');
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): string
    {
        return (string)$this->getAttribute('version');
    }

    /**
     * @inheritdoc
     */
    public function getScore(): float
    {
        if ($this->getAttribute('score') === null) {
            $this->setAttribute('score', $this->calculateScore() * $this->getWeight());
        }

        return (float)$this->getAttribute('score');
    }

    /**
     * @return array
     */
    public function toConfig(): array
    {
        return parent::toArray();
    }


    /*******************************************
     * METRICS (CHILDREN)
     *******************************************/

    /**
     * @return MetricInterface[]
     */
    public function getMetrics(): array
    {
        if ($this->metrics === null) {
            $this->setMetrics(
                $this->loadMetrics()
            );
        }

        return $this->metrics;
    }

    /**
     * @return array
     */
    protected function loadMetrics(): array
    {
        if ($this->getIsNewRecord()) {
            return (array)static::METRICS;
        }

        return array_filter(array_merge(
            $this->children,
            (array)ArrayHelper::getValue($this->settings, 'metrics', [])
        ));
    }

    /**
     * @param array $metrics
     * @return $this
     */
    public function setMetrics(array $metrics = [])
    {
        $this->metrics = [];

        foreach ($metrics as $metric) {
            if (!$metric instanceof MetricInterface) {
                $metric = $this->createMetric($metric);
            }

            $this->metrics[] = $metric;
        }

        return $this;
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param $metric
     * @return MetricInterface
     */
    protected function createMetric($metric): MetricInterface
    {
        if (is_string($metric)) {
            $metric = ['class' => $metric];
        }

        /** Pass along the element */
        if (is_array($metric) && !isset($metric['elementId'])) {
            $metric['element'] = $metric['element'] ?? $this->getElement();
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return MetricHelper::create($metric);
    }

    /*******************************************
     * CHILDREN (RECORDS)
     *******************************************/

    /**
     * @return ElementMetricQuery
     */
    public function getChildren(): ElementMetricQuery
    {
        /** @var ElementMetricQuery $query */
        $query = $this->hasMany(
            static::class,
            ['parentId' => 'id']
        );

        // Children have parents
        $query->parentId(':notempty:');

        return $query;
    }

    /*******************************************
     * VALIDATE (CHILD RECORDS)
     *******************************************/

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        $success = true;
        foreach ($this->getMetrics() as $metric) {
            if ($metric instanceof ElementMetric) {
                if (!$metric->validate()) {
                    $success = false;
                }
            }
        }

        return $success ? parent::beforeValidate() : false;
    }

    /*******************************************
     * SAVE (CHILD RECORDS)
     *******************************************/

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        $metrics = [];

        foreach ($this->getMetrics() as $metric) {
            if (!$metric instanceof ElementMetric) {
                $metrics[] = $metric->toConfig();
            }
        }
        /** @var array $metrics */
        /** @var ElementMetric[] $children */

        // Merge into settings
        if (!empty($metrics)) {
            $this->settings = array_filter(array_merge(
                (array)$this->settings,
                [
                    'metrics' => $metrics
                ]
            ));
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $children = [];

        foreach ($this->getMetrics() as $metric) {
            if ($metric instanceof ElementMetric) {
                $children[] = $metric;
            }
        }

        if (!empty($children)) {
            $success = true;
            foreach ($children as $child) {
                $child->parentId = $this->id;

                if (!$child->save()) {
                    $success = false;
                }
            }

            if (!$success) {
                $this->addError('children', 'Unable to save children');
            }
        }

        return parent::afterSave($insert, $changedAttributes);
    }
}
