<?php

namespace WebEtDesign\AnalyticsBundle\Enum;

use WebEtDesign\AnalyticsBundle\Entity\Block;

class BlockTypeEnum
{

    public const BROWSERS = 'browsers';
    public const COUNTRIES = 'countries';
    public const DEVICES = 'devices';
    public const PAGES = 'pages';
    public const SOURCES = 'sources';
    public const USERS = 'users';
    public const USER_WEEK = 'userWeek';
    public const USER_YEAR = 'userYear';

    public static $choices = [
        self::BROWSERS => 'Navigateurs',
        self::COUNTRIES => 'Pays',
        self::DEVICES => 'Appareils',
        self::PAGES => 'Pages vues',
        self::SOURCES => 'Sources de traffic',
        self::USERS => 'Utilisateurs par heure',
        self::USER_WEEK => 'Utilisateurs sur la semaine',
        self::USER_YEAR => 'Utilisateurs sur l\'année'
    ];

    public static function getChoicesList(array $exists = [], Block $subject = null)
    {
        $choices = array_flip(self::$choices);

        foreach ($exists as $exist) {
            if (!$exist instanceof Block || $subject->getCode() == $exist->getCode()) continue;

            unset($choices[self::getValue($exist->getCode())]);
        }

        return $choices;
    }

    public static function getValue($key){
        return array_key_exists($key, self::$choices) ? self::$choices[$key] : null;
    }
}
