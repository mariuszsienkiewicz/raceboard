import { useState } from "react";
import { ArrowRight, CalendarDays, Heart, MapPin } from "lucide-react";
import SearchReturnLink from "./SearchReturnLink";
import { Badge } from "@/components/ui/badge";
import { Skeleton } from "@/components/ui/skeleton";
import { cn } from "@/lib/utils";
import type { Race } from "@/types/race";

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
        <div className="my-1.5 flex flex-col gap-3 rounded-2xl border border-border bg-card p-5">
            <div className="flex items-start justify-between gap-4">
                <Skeleton className="h-5 w-2/3" />
                <Skeleton className="h-6 w-28 rounded-full" />
            </div>
            <Skeleton className="h-4 w-1/3" />
            <div className="flex gap-2">
                <Skeleton className="h-6 w-12 rounded-full" />
                <Skeleton className="h-6 w-20 rounded-full" />
            </div>
        </div>
    );
}

export default function SearchResult({ race }: { race: Race }) {
    const sortedDates = race.dates.slice().sort();
    const nextDate = sortedDates[0] ?? null;
    const extraDatesCount = sortedDates.length - 1;
    const [watched, setWatched] = useState(false);

    function handleWatchlistToggle(event: React.MouseEvent) {
        event.preventDefault();
        event.stopPropagation();
        setWatched((prev) => !prev);
    }

    return (
        <SearchReturnLink to={`/races/${race.id}`} className="my-1.5 block">
            <article
                className={cn(
                    "group flex cursor-pointer flex-col gap-3 rounded-2xl border border-border bg-card p-5 text-card-foreground",
                    "transition-all duration-200 hover:-translate-y-px hover:shadow-md",
                )}
            >
                <div className="flex items-start justify-between gap-4">
                    <h3 className="flex items-center gap-1.5 text-base leading-snug font-semibold text-foreground transition-colors group-hover:text-primary">
                        {race.name}
                        <ArrowRight className="size-4 shrink-0 -translate-x-1 opacity-0 transition-all duration-200 group-hover:translate-x-0 group-hover:opacity-100" />
                    </h3>
                    <div className="flex shrink-0 items-center gap-2">
                        {nextDate && (
                            <Badge className="h-6 gap-1.5 border-transparent bg-primary/10 px-2.5 text-primary hover:bg-primary/10">
                                <CalendarDays className="size-3.5" />
                                <span>{formatDate(nextDate)}</span>
                                {extraDatesCount > 0 && (
                                    <span className="opacity-60">+{extraDatesCount}</span>
                                )}
                            </Badge>
                        )}
                        <button
                            type="button"
                            onClick={handleWatchlistToggle}
                            aria-label={watched ? "Remove from watchlist" : "Add to watchlist"}
                            className={cn(
                                "rounded-full p-1.5 transition-colors duration-150 hover:bg-red-50",
                                watched ? "text-red-500" : "text-muted-foreground hover:text-red-400",
                            )}
                        >
                            <Heart className={cn("size-4", watched && "fill-current")} />
                        </button>
                    </div>
                </div>

                <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                    <MapPin className="size-4 shrink-0" />
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
                            <Badge key={km} variant="secondary">
                                {formatDistance(km)}
                            </Badge>
                        ))}
                    </div>
                )}
            </article>
        </SearchReturnLink>
    );
}
