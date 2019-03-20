<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\craft\scorecard\records;

use Craft;
use craft\helpers\DateTimeHelper;
use DateTime;
use flipbox\craft\ember\records\ActiveRecordWithId;
use flipbox\craft\ember\records\ElementAttributeTrait;
use flipbox\craft\scorecard\queries\ElementMetricQuery;
use flipbox\craft\scorecard\helpers\MetricHelper;
use flipbox\craft\scorecard\metrics\SavableMetricInterface;
use flipbox\craft\scorecard\validators\ElementMetricValidator;

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
 * @property DateTime $dateCalculated
 */
abstract class ElementMetric extends ActiveRecordWithId implements SavableMetricInterface
{
    use ElementAttributeTrait;

    /**
     * The default score weight
     */
    const WEIGHT = 1;

    /**
     * The default metric version
     */
    const VERSION = '1.0';

    /**
     * The table alias
     */
    const TABLE_ALIAS = 'scorecard_element_metrics';

    /**
     * The Active Query class
     */
    const ACTIVE_QUERY_CLASS = ElementMetricQuery::class;

    /**
     * @inheritdoc
     */
    protected $getterPriorityAttributes = ['elementId', 'score', 'dateCalculated'];

    /**
     * @return float
     */
    abstract protected function calculateScore(): float;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

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
    public static function populateRecord($record, $row)
    {
        parent::populateRecord($record, $row);

        $record->version = static::VERSION;
        $record->weight = static::WEIGHT;
        $record->class = static::class;
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     * @return ElementMetricQuery
     */
    public static function find()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Craft::createObject(
            static::ACTIVE_QUERY_CLASS,
            [
                get_called_class(),
                [
                    'class' => static::class
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function resetScore()
    {
        $this->setAttribute('score', null);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        // Make sure score is calculated
        $this->getScore();

        return parent::beforeSave($insert);
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
                        'version',
                        'dateCalculated'
                    ],
                    'safe',
                    'on' => [
                        self::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }


    /*******************************************
     * METRIC INTERFACE
     *******************************************/

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public static function displayName(): string
    {
        return preg_replace(
            '/(?<!^)([A-Z])/',
            ' $0',
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
     * @return DateTime
     */
    public function getDateCalculated(): DateTime
    {
        if (null === ($dateCalculated = $this->getAttribute('dateCalculated'))) {
            $dateCalculated = $this->defaultDateCalculated();
            $this->setAttribute('dateCalculated', $dateCalculated);
        }

        if (!$dateCalculated instanceof DateTime) {
            if (is_array($dateCalculated)) {
                $dateCalculated = $dateCalculated['date'] ?? $dateCalculated;
            }

            $dateCalculated = DateTimeHelper::toDateTime($dateCalculated);
            $this->setAttribute('dateCalculated', $dateCalculated);
        }

        return $dateCalculated;
    }

    /**
     * @return DateTime
     */
    protected function defaultDateCalculated(): DateTime
    {
        return DateTimeHelper::toDateTime(
            DateTimeHelper::currentUTCDateTime()->format('Y-m-d')
        );
    }


    /*******************************************
     * SETTINGS
     *******************************************/

    /**
     * @param string $attribute
     * @return mixed
     */
    public function getSettingsValue(string $attribute)
    {
        $settings = MetricHelper::resolveSettings(
            $this->getAttribute('settings')
        );

        return $settings[$attribute] ?? null;
    }

    /**
     * @param string $attribute
     * @param $value
     * @return $this
     */
    public function setSettingsValue(string $attribute, $value)
    {
        $settings = MetricHelper::resolveSettings(
            $this->getAttribute('settings')
        );
        $settings[$attribute] = $value;
        $this->setAttribute('settings', $settings);

        return $this;
    }


    /*******************************************
     * CONFIGURATION
     *******************************************/

    /**
     * @return array
     */
    public function toConfig(): array
    {
        return $this->toArray();
    }
}
