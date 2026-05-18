export interface Race {
    id: string;
    name: string;
    slug: string;
    city: string;
    voivodeship: string;
    dates: string[];
    distances: number[];
}

export interface RaceDetails {
    id: string;
    name: string;
    slug: string;
    city: string;
    country: string;
    voivodeship: string;
    editions: Editions[];
}

export interface Editions {
    date: string;
    distances: Distance[];
}

export interface Distance {
    id: string;
    name: string;
    lengthInKm: number;
}
