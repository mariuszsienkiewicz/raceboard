import { HeartIcon } from "@heroicons/react/24/outline";
import { useAuth } from "../../context/useAuth";
import { useWatchlist } from "../../hooks/useWatchlist";
import { HeartIcon as HeartSolidIcon } from "@heroicons/react/24/solid";

interface WatchlistButtonProps {
    raceId: string;
}

export default function WatchlistButton({ raceId }: WatchlistButtonProps) {
    const { isAuthenticated } = useAuth();
    const { watched, toggle } = useWatchlist(raceId);

    if (!isAuthenticated) {
        return null; // Nie pokazuj przycisku, jeśli użytkownik nie jest zalogowany
    }

    if (watched === null) {
        return <button disabled className="px-4 py-2 rounded-full border text-sm font-medium">Loading...</button>;
    }

    return (
        <button
            onClick={toggle}
            aria-label={watched ? "Remove from watchlist" : "Add to watchlist"}
            className={`flex shrink-0 items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition-all duration-150 ${watched
                    ? "border-red-200 bg-red-50 text-red-500 hover:bg-red-100"
                    : "border-border bg-surface text-muted hover:border-red-200 hover:bg-red-50 hover:text-red-400"
                }`}
        >
            {watched
                ? <HeartSolidIcon className="size-4" />
                : <HeartIcon className="size-4" />
            }
            <span className="hidden sm:inline">{watched ? "Watching" : "Watch"}</span>
        </button>
    );
}