import { useEffect, useState } from "react";
import { Link } from "react-router-dom";
import { ArrowRight, Heart } from "lucide-react";
import WatchlistCard from "@/components/watchlist/WatchlistCard";
import { Button, buttonVariants } from "@/components/ui/button";
import { Skeleton } from "@/components/ui/skeleton";
import { cn } from "@/lib/utils";
import PageSeo from "@/components/PageSeo";
import { apiFetch } from "@/api/client";
import { useAuth } from "@/context/useAuth";
import type { WatchlistEntry } from "@/types/watchlist";

async function fetchWatchlist(): Promise<WatchlistEntry[]> {
    return apiFetch("/api/me/watchlist")
        .then((res) => res.json())
        .then((data: WatchlistEntry[]) => data)
        .catch((err) => {
            console.error("Failed to fetch watchlist:", err);
            return [];
        });
}

function WatchlistSkeleton() {
    return (
        <div className="flex flex-col gap-3">
            {Array.from({ length: 3 }).map((_, index) => (
                <div
                    key={index}
                    className="flex flex-col gap-3 rounded-2xl border border-border bg-card p-5"
                >
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
            ))}
        </div>
    );
}

function EmptyWatchlist() {
    return (
        <div className="flex flex-col items-center gap-5 py-20 text-center">
            <div className="flex size-16 items-center justify-center rounded-full border border-border bg-muted">
                <Heart className="size-8 text-muted-foreground/40" />
            </div>
            <div className="flex flex-col gap-1.5">
                <p className="font-semibold text-foreground">Your watchlist is empty</p>
                <p className="max-w-xs text-sm leading-relaxed text-muted-foreground">
                    Browse races and click the heart icon to save ones you want to run.
                </p>
            </div>
            <Button variant="outline" render={<Link to="/" />} nativeButton={false}>
                Browse races
                <ArrowRight />
            </Button>
        </div>
    );
}

function NotAuthenticated() {
    return (
        <div className="flex flex-col items-center gap-5 py-20 text-center">
            <div className="flex size-16 items-center justify-center rounded-full bg-primary/10">
                <Heart className="size-8 text-primary" />
            </div>
            <div className="flex flex-col gap-1.5">
                <p className="font-semibold text-foreground">Log in to see your watchlist</p>
                <p className="max-w-xs text-sm leading-relaxed text-muted-foreground">
                    Save races you want to run and track them all in one place.
                </p>
            </div>
            <Link to="/login" className={cn(buttonVariants({ size: "sm" }))}>
                Log in
            </Link>
        </div>
    );
}

export default function WatchlistPage() {
    const { isAuthenticated } = useAuth();
    const [entries, setEntries] = useState<WatchlistEntry[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        if (!isAuthenticated) {
            return;
        }

        let cancelled = false;

        const timeout = setTimeout(() => {
            setLoading(true);
            fetchWatchlist()
                .then((data) => {
                    if (!cancelled) {
                        setEntries(data);
                    }
                })
                .catch(() => {
                    if (!cancelled) {
                        setEntries([]);
                    }
                })
                .finally(() => {
                    if (!cancelled) {
                        setLoading(false);
                    }
                });
        }, 0);

        return () => {
            cancelled = true;
            clearTimeout(timeout);
        };
    }, [isAuthenticated]);

    const handleRemove = (raceId: string) => {
        apiFetch(`/api/me/watchlist/${raceId}`, { method: "DELETE" })
            .then((res) => {
                if (!res.ok) {
                    throw new Error("Failed to remove from watchlist");
                }
                setEntries((prev) => prev.filter((entry) => entry.raceId !== raceId));
            })
            .catch((err) => {
                console.error(err);
                alert("Failed to remove from watchlist. Please try again.");
            });
    };

    return (
        <div className="flex flex-col gap-8 py-4">
            <PageSeo
                title="Watchlist"
                description="Your saved running races on Raceboard."
                noIndex
            />
            <div className="flex flex-col gap-1">
                <h1 className="text-2xl font-bold tracking-tight text-foreground">Watchlist</h1>
                <p className="text-sm text-muted-foreground">Races you want to run.</p>
            </div>

            {!isAuthenticated ? (
                <NotAuthenticated />
            ) : loading ? (
                <WatchlistSkeleton />
            ) : entries.length === 0 ? (
                <EmptyWatchlist />
            ) : (
                <>
                    <p className="text-xs text-muted-foreground">
                        {entries.length} {entries.length === 1 ? "race" : "races"} saved
                    </p>
                    <div className="flex flex-col gap-3">
                        {entries.map((entry) => (
                            <WatchlistCard
                                key={entry.id}
                                entry={entry}
                                onRemove={() => handleRemove(entry.raceId)}
                            />
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}
