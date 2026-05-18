import type { WatchlistEntry } from "../../types/watchlist";
import {
    MapPinIcon,
    ArrowRightIcon,
} from "@heroicons/react/24/outline";
import { HeartIcon as HeartSolidIcon } from "@heroicons/react/24/solid";
import { Surface } from "@heroui/react/surface";
import { Link } from "react-router-dom";

export default function WatchlistCard({ entry, onRemove }: { entry: WatchlistEntry; onRemove: (id: string) => void }) {
    const { race } = entry;

    return (
        <Link to={`/races/${race.id}`} className="block">
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
                        <button
                            onClick={(e) => { e.preventDefault(); e.stopPropagation(); onRemove(entry.id); }}
                            aria-label="Remove from watchlist"
                            className="rounded-full p-1.5 text-red-500 transition-colors duration-150 hover:bg-red-50"
                        >
                            <HeartSolidIcon className="size-4" />
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
            </Surface>
        </Link>
    );
}