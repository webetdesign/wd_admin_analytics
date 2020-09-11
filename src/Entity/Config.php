<?php

namespace WebEtDesign\AnalyticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use WebEtDesign\AnalyticsBundle\Enum\ConfigTypeEnum;

/**
 * @ORM\Entity()
 * @ORM\Table(name="analytics__config")
 */
class Config
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     *
     */
    private $code;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    private $value;



    public function __toString()
    {
        $label = ConfigTypeEnum::getValue($this->code) ?? null;
        return $label ? $label : 'config';
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getCodeList(): ?string
    {
        return ConfigTypeEnum::getValue($this->code);
    }
    /**
     * @param string $code
     */
    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getValueArray(){
        return json_decode( $this->value, true);
    }

    public function getValueFormated(){
        foreach ($this->getValueArray() as $datum) {
          $formatted[] = $this->formatColor($datum);
        }

        return $formatted;
    }

    public function formatColor($datum){
        $hex      = str_replace('#', '', $datum);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));

        return 'rgb(' . sprintf('%03s', $rgb['r']) . ", " . sprintf('%03s', $rgb['g']) . ', ' . sprintf('%03s', $rgb['b']) . ')';
    }

    public function getValueString(){
        $value = "";
        foreach ($this->getValueArray() as $item) {
            $value .= $item . " ";
        }
        return $value;
    }

    /**
     * @param string|null $value
     */
    public function setValue($value): void
    {
        $this->value = is_array($value) ? json_encode($value) : $value;
    }

}
