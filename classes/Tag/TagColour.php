<?php

declare(strict_types=1);

namespace MyLittleWallpaper\classes\Tag;

/**
 * Colour tag class.
 */
class TagColour extends AbstractTag
{
    /**
     * @var string
     */
    private string $colour = '';

    /**
     * @var string
     */
    private string $similarColour = '';

    /**
     * @var float
     */
    private float $amount = .0;

    /**
     * @param string|null $colour
     * @param float|null  $amount
     * @param string|null $similar_colour
     */
    public function __construct(?string $colour = null, ?float $amount = null, ?string $similar_colour = null)
    {
        if ($colour !== null) {
            $this->setColourHex($colour);
            if ($amount !== null) {
                $this->setAmount($amount);
            }
            if ($similar_colour !== null) {
                $this->setSimilarColourHex($similar_colour);
            } else {
                $this->setSimilarColourHex($colour);
            }
        }
        parent::__construct();
    }

    /**
     * Returns false if colour isn't set.
     * @return string|bool
     */
    public function getColourHex()
    {
        if ($this->colour === '') {
            return false;
        }
        return $this->colour;
    }

    /**
     * Returns false if colour isn't set.
     * @return string|bool
     */
    public function getSimilarColourHex()
    {
        if ($this->similarColour === '') {
            return false;
        }
        return $this->similarColour;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param string $colour
     *
     * @return bool
     */
    public function setColourHex(string $colour): bool
    {
        if (preg_match("/^#?[0-9a-f]{6}$/", $colour)) {
            if (strpos($colour, '#') === true) {
                $colour = substr($colour, 1);
            }
            $this->colour = strtolower($colour);
            return true;
        }

        if (preg_match("/^#?[0-9a-f]{3}$/", $colour)) {
            if (strpos($colour, '#') === true) {
                $colour = substr($colour, 1);
            }
            $this->colour = str_repeat($colour[0], 2) . str_repeat($colour[1], 2) .
                str_repeat($colour[2], 2);
            return true;
        }
        return false;
    }

    /**
     * @param string $colour
     *
     * @return bool
     */
    public function setSimilarColourHex(string $colour): bool
    {
        if (preg_match("/^#?[0-9a-f]{6}$/", $colour)) {
            if (strpos($colour, '#') === true) {
                $colour = substr($colour, 1);
            }
            $this->similarColour = strtolower($colour);
            return true;
        }

        if (preg_match("/^#?[0-9a-f]{3}$/", $colour)) {
            if (strpos($colour, '#') === true) {
                $colour = substr($colour, 1);
            }
            $this->similarColour = str_repeat($colour[0], 2) . str_repeat($colour[1], 2) .
                str_repeat($colour[2], 2);
            return true;
        }
        return false;
    }

    /**
     * @param float $amount
     *
     * @return void
     */
    public function setAmount(float $amount): void
    {
        if ($amount >= 0 && $amount <= 100) {
            $this->amount = $amount;
        }
    }
}
