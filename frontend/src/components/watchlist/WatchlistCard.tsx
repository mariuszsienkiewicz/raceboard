import type { WatchlistEntry } from "@/types/watchlist";
import { ArrowRight, Heart, MapPin } from "lucide-react";
import { Link } from "react-router-dom";
import { cn } from "@/lib/utils";

interface WatchlistCardProps {
    entry: WatchlistEntry;
    onRemove: () => void;
}

export default function WatchlistCard({ entry, onRemove }: WatchlistCardProps) {
    const { race } = entry;

    return (
        <Link to={`/races/${race.id}`} className="block">
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
                    <button
                        type="button"
                        onClick={(event) => {
                            event.preventDefault();
                            event.stopPropagation();
                            onRemove();
                        }}
                        aria-label="Remove from watchlist"
                        className="rounded-full p-1.5 text-red-500 transition-colors duration-150 hover:bg-red-50"
                    >
                        <Heart className="size-4 fill-current" />
                    </button>
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
            </article>
        </Link>
    );
}
