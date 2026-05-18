import type { RaceDetails } from "./race";

export interface WatchlistEntry {
    id: string;
    raceId: string;
    race: RaceDetails;
}