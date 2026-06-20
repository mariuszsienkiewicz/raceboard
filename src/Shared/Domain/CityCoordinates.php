<?php

declare(strict_types=1);

namespace App\Shared\Domain;

class CityCoordinates
{
    /** @var array<string, array{lat: float, lng: float}> */
    private const array COORDINATES = [
        'warszawa' => ['lat' => 52.2297, 'lng' => 21.0122],
        'kraków' => ['lat' => 50.0647, 'lng' => 19.9450],
        'gdańsk' => ['lat' => 54.3520, 'lng' => 18.6466],
        'wrocław' => ['lat' => 51.1079, 'lng' => 17.0385],
        'poznań' => ['lat' => 52.4064, 'lng' => 16.9252],
        'łódź' => ['lat' => 51.7592, 'lng' => 19.4560],
        'szczecin' => ['lat' => 53.4285, 'lng' => 14.5528],
        'bydgoszcz' => ['lat' => 53.1235, 'lng' => 18.0084],
        'lublin' => ['lat' => 51.2465, 'lng' => 22.5684],
        'białystok' => ['lat' => 53.1325, 'lng' => 23.1688],
        'katowice' => ['lat' => 50.2649, 'lng' => 19.0238],
        'gdynia' => ['lat' => 54.5189, 'lng' => 18.5305],
        'toruń' => ['lat' => 53.0138, 'lng' => 18.5984],
        'rzeszów' => ['lat' => 50.0412, 'lng' => 21.9991],
        'kielce' => ['lat' => 50.8661, 'lng' => 20.6286],
        'olsztyn' => ['lat' => 53.7784, 'lng' => 20.4801],
        'opole' => ['lat' => 50.6751, 'lng' => 17.9213],
        'zielona góra' => ['lat' => 51.9356, 'lng' => 15.5062],
        'gorzów wielkopolski' => ['lat' => 52.7325, 'lng' => 15.2369],
    ];

    /** @return array{lat: float, lng: float}|null */
    public static function get(string $city): ?array
    {
        return self::COORDINATES[mb_strtolower(trim($city))] ?? null;
    }
}