<?php

namespace WebEtDesign\AnalyticsBundle\Enum;

use WebEtDesign\AnalyticsBundle\Entity\Block;

class BlockStartEnum
{
    public const YESTERDAY = 'yesterday';
    public const SEVEN_DAYS_AGO = '7 days ago';
    public const TWENTY_EIGHT_DAYS_AGO = '28 days ago';
    public const NINETY_DAYS_AGO = '90 days ago';

    public static $choices = [
        self::YESTERDAY => 'Hier',
        self::SEVEN_DAYS_AGO => '7 derniers jours',
        self::TWENTY_EIGHT_DAYS_AGO => '28 derniers jours',
        self::NINETY_DAYS_AGO => '90 derniers jours',

    ];

    public static function getChoicesList()
    {
        return  array_flip(self::$choices);
    }

    public static function getValue($key){
        return array_key_exists($key, self::$choices) ? self::$choices[$key] : null;
    }
}
