<?php

namespace DnaKlik\DnaExchangeBundle\Service;

interface StampProviderInterface
{
    /**
     * Returns an array of stamps
     *
     * @return array
     */
    public function getItemStamps($criteria): array;

    /**
     * Returns an array of stamps after crossover
     *
     * @return array
     */
    public function getStampsAfterCrossover($criteria, $userStamps): array;

    /**
     * Returns stamp.
     *
     * @return string
     */
    public function getStamp($criteria): string;
}