<?php

namespace DnaKlik\DnaExchangeBundle\Event;

final class DnaKlikDnaExchangeEvents
{
    /**
     * Called directly before the Dna Exchange API data is returned.
     *
     * Listeners have the opportunity to change that data.
     *
     * @Event("DnaKlik\DnaExchangeBundle\Event\FilterApiResponseEvent")
     */
    const FILTER_API = 'dnaklik_dna_exchange.filter_api';
}
