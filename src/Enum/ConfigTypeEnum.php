<?php

namespace WebEtDesign\AnalyticsBundle\Enum;

use WebEtDesign\AnalyticsBundle\Entity\Block;
use WebEtDesign\AnalyticsBundle\Entity\Config;

class ConfigTypeEnum
{

    public const COLORS = 'colors';
    public const DIFF_COLORS = 'DIFF_COLORS';
    public const DEGRADED_COLOR = 'DEGRADED_COLOR';

    public static $choices = [
        self::COLORS => 'Couleurs',
        self::DIFF_COLORS => 'Couleurs pour les comparaisons',
        self::DEGRADED_COLOR => 'Couleur de dégradé',
    ];

    public static function getChoicesList(array $exists = [], Config $subject = null)
    {
        $choices = array_flip(self::$choices);

        foreach ($exists as $exist) {
            if (!$exist instanceof Config || $subject->getCode() == $exist->getCode()) continue;

            unset($choices[self::getValue($exist->getCode())]);
        }

        return $choices;
    }

    public static function getValue($key){
        return array_key_exists($key, self::$choices) ? self::$choices[$key] : null;
    }
}
