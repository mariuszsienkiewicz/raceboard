import { Chip, Surface, Skeleton } from "@heroui/react";
import type { Race } from "../../types/race";
import { MapPinIcon, CalendarDaysIcon, ArrowRightIcon, HeartIcon } from "@heroicons/react/24/outline";
import { HeartIcon as HeartSolidIcon } from "@heroicons/react/24/solid";
import { useState } from "react";
import SearchReturnLink from "./SearchReturnLink";

function formatDate(dateStr: string): string {
    return new Date(Number(dateStr) * 1000).toLocaleDateString("pl-PL", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
}

function formatDistance(km: number): string {
    if (km === 42.195) return "Marathon";
    if (km === 21.0975) return "Half";
    return Number.isInteger(km) ? `${km} km` : `${km} km`;
}

export function SearchResultSkeleton() {
    return (
        <Surface className="flex flex-col gap-3 rounded-2xl p-5 my-1.5" variant="default">
            <div className="flex items-start justify-between gap-4">
                <Skeleton className="h-5 w-2/3 rounded-lg" />
                <Skeleton className="h-6 w-28 rounded-full" />
            </div>
            <Skeleton className="h-4 w-1/3 rounded-lg" />
            <div className="flex gap-2">
                <Skeleton className="h-6 w-12 rounded-full" />
                <Skeleton className="h-6 w-20 rounded-full" />
            </div>
        </Surface>
    );
}

export default function SearchResult({ race }: { race: Race }) {
    const sortedDates = race.dates.slice().sort();
    const nextDate = sortedDates[0] ?? null;
    const extraDatesCount = sortedDates.length - 1;
    const [watched, setWatched] = useState(false);

    function handleWatchlistToggle(e: React.MouseEvent) {
        e.preventDefault();
        e.stopPropagation();
        setWatched((prev) => !prev);
    }

    return (
        <SearchReturnLink to={`/races/${race.id}`} className="block my-1.5">
            <Surface
                className="group flex flex-col gap-3 rounded-2xl p-5 cursor-pointer transition-all duration-200 hover:shadow-md hover:-translate-y-px"
                variant="default"
            >
                <div className="flex items-start justify-between gap-4">
                    <h3 className="flex items-center gap-1.5 text-base font-semibold leading-snug text-foreground transition-colors group-hover:text-primary">
                        {race.name}
                        <ArrowRightIcon className="size-4 shrink-0 opacity-0 -translate-x-1 transition-all duration-200 group-hover:opacity-100 group-hover:translate-x-0" />
                    </h3>
                    <div className="flex shrink-0 items-center gap-2">
                        {nextDate && (
                            <div className="flex items-center gap-1.5 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary">
                                <CalendarDaysIcon className="size-3.5" />
                                <span>{formatDate(nextDate)}</span>
                                {extraDatesCount > 0 && (
                                    <span className="opacity-60">+{extraDatesCount}</span>
                                )}
                            </div>
                        )}
                        <button
                            onClick={handleWatchlistToggle}
                            aria-label={watched ? "Remove from watchlist" : "Add to watchlist"}
                            className={`rounded-full p-1.5 transition-colors duration-150 hover:bg-red-50 ${watched ? "text-red-500" : "text-muted hover:text-red-400"}`}
                        >
                            {watched
                                ? <HeartSolidIcon className="size-4" />
                                : <HeartIcon className="size-4" />
                            }
                        </button>
                    </div>
                </div>

                <div className="flex items-center gap-1.5 text-sm text-muted">
                    <MapPinIcon className="size-4 shrink-0" />
                    <span>
                        {race.city}
                        {race.voivodeship && (
                            <span className="opacity-60">, {race.voivodeship}</span>
                        )}
                    </span>
                </div>

                {race.distances.length > 0 && (
                    <div className="flex flex-wrap gap-1.5">
                        {race.distances.map((km) => (
                            <Chip key={km} size="sm" variant="soft">
                                {formatDistance(km)}
                            </Chip>
                        ))}
                    </div>
                )}
            </Surface>
        </SearchReturnLink>
    );
}