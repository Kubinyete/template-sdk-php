<?php

namespace Teamipag\Sdk\Util;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;

abstract class DateUtil
{
    public const ISO_DATE_FORMAT = 'Y-m-d';

    /**
     * Parses a date string into a DateTimeInterface object. If the input is already a DateTimeInterface, it is returned as-is.
     *
     * @param mixed $date
     * @param string $format
     * @return DateTimeInterface
     */
    public static function parseDate(mixed $date, string $format = self::ISO_DATE_FORMAT): DateTimeInterface
    {
        $date = self::tryParseDate($date, $format);

        if (!$date) {
            throw new InvalidArgumentException("Invalid date format ($date does not conform to $format)");
        }

        return $date;
    }

    /**
     * Tries to parse a date string into a DateTimeInterface object. If the input is already a DateTimeInterface, it is returned as-is.
     *
     * @param mixed $date
     * @param string $format
     * @return DateTimeInterface|null
     */
    public static function tryParseDate(mixed $date, string $format = self::ISO_DATE_FORMAT): ?DateTimeInterface
    {
        if ($date instanceof DateTimeInterface) {
            return $date;
        }

        if (is_null($date) || !is_string($date)) {
            return null;
        }

        return DateTime::createFromFormat($format, $date) ?: null;
    }
}
