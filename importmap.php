<?php

/**
 * Returns the importmap for this application.
 *
 * - "path" is a path inside the asset mapper system. Use the
 *     "debug:asset-map" command to see the full list of paths.
 *
 * - "entrypoint" (JavaScript only) set to true for any module that will
 *     be used as an "entrypoint" (and passed to the importmap() Twig function).
 *
 * The "importmap:require" command can be used to add new entries to this file.
 */
return [
    '@fortawesome/fontawesome-free/css/all.css' => [
        'version' => '6.6.0',
        'type' => 'css',
    ],
    '@fortawesome/free-solid-svg-icons' => [
        'version' => '6.7.2',
    ],
    '@fortawesome/fontawesome-free' => [
        'version' => '6.7.2',
    ],
    '@fortawesome/fontawesome-free/css/fontawesome.min.css' => [
        'version' => '6.7.2',
        'type' => 'css',
    ],
];
