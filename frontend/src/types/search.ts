import type { Race } from "./race";

export interface SearchResponse {
    hits: Race[];
    totalHits: number;
    page: number;
    perPage: number;
}
