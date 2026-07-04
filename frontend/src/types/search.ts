import type { Race } from "./race";

export interface SearchResponse {
    hits: Race[];
    totalHits: number;
    page: number;
    perPage: number;
    totalPages: number;
}

export interface MapSearchPoint {
    id: string;
    name: string;
    city: string;
    _geo?: { lat: number; lng: number } | null;
}
