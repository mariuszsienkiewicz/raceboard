import { useCallback, useEffect, useMemo, useState } from "react";
import { apiFetch } from "@/api/client";
import { useAuth } from "@/context/useAuth";

const EMPTY_WATCHED_IDS = new Set<string>();

export function useWatchlistStatuses(raceIds: string[]) {
    const { isAuthenticated } = useAuth();
    const [watchedIds, setWatchedIds] = useState<Set<string>>(() => new Set());
    const raceIdsKey = useMemo(() => [...raceIds].sort().join(","), [raceIds]);
    const shouldFetch = isAuthenticated && raceIdsKey !== "";

    useEffect(() => {
        if (!shouldFetch) {
            return;
        }

        const ids = raceIdsKey.split(",");
        const controller = new AbortController();

        apiFetch("/api/me/watchlist/check", {
            method: "POST",
            body: JSON.stringify({ raceIds: ids }),
            signal: controller.signal,
        })
            .then((res) => {
                if (!res.ok) {
                    throw new Error("Failed to fetch watchlist statuses");
                }
                return res.json();
            })
            .then((data: { watchedRaceIds: string[] }) => {
                setWatchedIds(new Set(data.watchedRaceIds ?? []));
            })
            .catch((err) => {
                if (err instanceof Error && err.name === "AbortError") {
                    return;
                }
                console.error(err);
                setWatchedIds(new Set());
            });

        return () => controller.abort();
    }, [shouldFetch, raceIdsKey]);

    const visibleWatchedIds = shouldFetch ? watchedIds : EMPTY_WATCHED_IDS;

    const isWatched = useCallback(
        (raceId: string) => visibleWatchedIds.has(raceId),
        [visibleWatchedIds],
    );

    const toggle = useCallback(
        async (raceId: string) => {
            if (!isAuthenticated) {
                return;
            }

            const currentlyWatched = watchedIds.has(raceId);
            const method = currentlyWatched ? "DELETE" : "POST";

            try {
                const res = await apiFetch(`/api/me/watchlist/${raceId}`, { method });
                if (!res.ok) {
                    console.error("Failed to toggle watchlist status");
                    return;
                }

                setWatchedIds((prev) => {
                    const next = new Set(prev);
                    if (currentlyWatched) {
                        next.delete(raceId);
                    } else {
                        next.add(raceId);
                    }
                    return next;
                });
            } catch (err) {
                console.error(err);
            }
        },
        [isAuthenticated, watchedIds],
    );

    return { isAuthenticated, isWatched, toggle };
}
