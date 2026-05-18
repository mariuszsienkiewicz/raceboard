import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { Skeleton } from "@heroui/react";
import { Surface } from "@heroui/react/surface";
import {
    HeartIcon,
    ArrowRightIcon,
} from "@heroicons/react/24/outline";
import { useAuth } from "../context/useAuth";
import type { RaceDetails } from "../types/race";
import { apiFetch } from "../api/client";
import type { WatchlistEntry } from "../types/watchlist";
import WatchlistCard from "../components/watchlist/WatchlistCard";

async function fetchWatchlist(): Promise<WatchlistEntry[]> {
    return apiFetch("/api/me/watchlist")
        .then((res) => res.json())
        .then((data: WatchlistEntry[]) => data)
        .then((data) => {
            // fetch race details for each entry
            return Promise.all(
                data.map((entry) =>
                    apiFetch(`/api/races/${entry.raceId}`)
                        .then((res) => res.json())
                        .then((race: RaceDetails) => ({ ...entry, race }))
                )
            );
        })
        .catch((err) => {
            console.error("Failed to fetch watchlist:", err);
            return [];
        });
}

function WatchlistSkeleton() {
    return (
        <div className="flex flex-col gap-3">
            {Array.from({ length: 3 }).map((_, i) => (
                <Surface key={i} variant="default" className="flex flex-col gap-3 rounded-2xl p-5">
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
            ))}
        </div>
    );
}

function EmptyWatchlist() {
    return (
        <div className="flex flex-col items-center gap-5 py-20 text-center">
            <div className="flex size-16 items-center justify-center rounded-full bg-surface border border-border">
                <HeartIcon className="size-8 text-muted" style={{ opacity: 0.4 }} />
            </div>
            <div className="flex flex-col gap-1.5">
                <p className="font-semibold text-foreground">Your watchlist is empty</p>
                <p className="text-sm text-muted leading-relaxed max-w-xs">
                    Browse races and click the heart icon to save ones you want to run.
                </p>
            </div>
            <Link
                to="/"
                className="inline-flex items-center gap-1.5 rounded-xl border border-border bg-surface px-4 py-2 text-sm font-medium text-foreground hover:bg-background transition-colors"
            >
                Browse races
                <ArrowRightIcon className="size-4" />
            </Link>
        </div>
    );
}

function NotAuthenticated() {
    return (
        <div className="flex flex-col items-center gap-5 py-20 text-center">
            <div className="flex size-16 items-center justify-center rounded-full bg-primary/10">
                <HeartIcon className="size-8 text-primary" />
            </div>
            <div className="flex flex-col gap-1.5">
                <p className="font-semibold text-foreground">Log in to see your watchlist</p>
                <p className="text-sm text-muted leading-relaxed max-w-xs">
                    Save races you want to run and track them all in one place.
                </p>
            </div>
        </div>
    );
}

export default function WatchlistPage() {
    const { isAuthenticated } = useAuth();
    const [entries, setEntries] = useState<WatchlistEntry[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!isAuthenticated) { setLoading(false); return; }
        fetchWatchlist()
            .then(setEntries)
            .catch(() => setEntries([]))
            .finally(() => setLoading(false));
    }, [isAuthenticated]);

    const handleRemove = (raceId: string) => {
        apiFetch(`/api/me/watchlist/${raceId}`, { method: "DELETE" })
            .then((res) => {
                if (!res.ok) {
                    throw new Error("Failed to remove from watchlist");
                }
                setEntries((prev) => prev.filter((e) => e.raceId !== raceId));
            })
            .catch((err) => {
                console.error(err);
                alert("Failed to remove from watchlist. Please try again.");
            });
    };

    return (
        <div className="flex flex-col gap-8 py-4">
            <div className="flex flex-col gap-1">
                <h1 className="text-2xl font-bold tracking-tight text-foreground">Watchlist</h1>
                <p className="text-sm text-muted">Races you want to run.</p>
            </div>

            {!isAuthenticated ? (
                <NotAuthenticated />
            ) : loading ? (
                <WatchlistSkeleton />
            ) : entries.length === 0 ? (
                <EmptyWatchlist />
            ) : (
                <>
                    <p className="text-xs text-muted">
                        {entries.length} {entries.length === 1 ? "race" : "races"} saved
                    </p>
                    <div className="flex flex-col gap-3">
                        {entries.map((entry) => (
                            <WatchlistCard key={entry.id} entry={entry} onRemove={() => handleRemove(entry.raceId)} />
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}
