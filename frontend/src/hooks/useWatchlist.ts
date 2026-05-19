import { useEffect, useState } from "react";
import { apiFetch } from "../api/client";

export function useWatchlist(raceId: string) {
    const [watched, setWatched] = useState<boolean | null>(null); // null = loading

    useEffect(() => {
        apiFetch(`/api/me/watchlist/${raceId}/check`)
            .then(res => res.json())
            .then(data => setWatched(data.watched))
            .catch(() => setWatched(false));
    }, [raceId]);

    const toggle = async () => {
        const method = watched ? "DELETE" : "POST";
        apiFetch(`/api/me/watchlist/${raceId}`, { method })
            .then((res) => {
                // post - returns json with the id of the entry, delete returns 204 no content
                if (res.ok) {
                    setWatched((prev) => !prev);
                } else {
                    console.error("Failed to toggle watchlist status");
                }
            })
            .catch((err) => console.error(err));
    };

    return { watched, toggle };
}
