<?php

namespace WebEtDesign\AnalyticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use WebEtDesign\AnalyticsBundle\Enum\BlockStartEnum;
use WebEtDesign\AnalyticsBundle\Enum\BlockTypeEnum;

/**
 * @ORM\Entity()
 * @ORM\Table(name="analytics__block")
 */
class Block
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
    private $start;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    private $icon;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    private $size;

    /**
     * @var boolean|null
     * @ORM\Column(type="boolean", nullable=true)
     *
     */
    private $active;

    public function __toString()
    {
        $label = BlockTypeEnum::getValue($this->code) ?? null;
        return $label ? $label : 'block';
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
        return BlockTypeEnum::getValue($this->code);
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
    public function getStart(): ?string
    {
        return $this->start;
    }


    public function getStartList(): ?string
    {
        return BlockStartEnum::getValue($this->start);
    }

    /**
     * @param string|null $start
     */
    public function setStart(?string $start): void
    {
        $this->start = $start;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     */
    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    /**
     * @return string|null
     */
    public function getSize(): ?string
    {
        return $this->size;
    }

    /**
     * @param string|null $size
     */
    public function setSize(?string $size): void
    {
        $this->size = $size;
    }

    /**
     * @return bool|null
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @param bool|null $active
     */
    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }



}
