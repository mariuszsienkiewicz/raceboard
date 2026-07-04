import { Separator, type DateRange, type Key } from "@heroui/react";
import SearchBar from "./SearchBar";
import { useCallback, useEffect, useState } from "react";
import { apiFetch } from "../../api/client";
import SearchResult, { SearchResultSkeleton } from "./SearchResult";
import type { Race } from "../../types/race";
import type { SearchResponse } from "../../types/search";
import SearchPagination from "./SearchPagination";
import MapSearchView from "./MapSearchView";
import SearchResultsToolbar from "./SearchResultsToolbar";
import { restoreSearchScrollPosition, useSearchState } from "../../hooks/useSearchState";
import type { SearchMode } from "./SearchModeSwitcher";
import EmptyState from "../EmptyState";

const SKELETON_COUNT = 20;
const PER_PAGE = 20;

export default function Search() {
    const {
        q: searchTerm,
        mode: searchMode,
        distances: selectedDistances,
        voivodeships: selectedVoivodeships,
        dateRange: selectedDateRange,
        page,
        mapBounds,
        updateState,
    } = useSearchState();

    const [searchResponse, setSearchResponse] = useState<SearchResponse | null>(null);
    const [results, setResults] = useState<Race[]>([]);
    const [loading, setLoading] = useState(true);

    const handleSearchTermChange = useCallback(
        (value: string) => updateState({ q: value, page: 1 }),
        [updateState],
    );

    const handleDistanceChange = useCallback(
        (selected: Set<Key>) => updateState({ distances: selected, page: 1 }),
        [updateState],
    );

    const handleVoivodeshipChange = useCallback(
        (selected: Set<Key>) => updateState({ voivodeships: selected, page: 1 }),
        [updateState],
    );

    const handleDateChange = useCallback(
        (selected: DateRange | null) => updateState({ dateRange: selected, page: 1 }),
        [updateState],
    );

    const handlePageChange = useCallback(
        (newPage: number) => {
            setLoading(true);
            updateState({ page: newPage });
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
        [updateState],
    );

    const handleModeChange = useCallback(
        (mode: SearchMode) => updateState({ mode, page: 1 }),
        [updateState],
    );

    const handleMapBoundsChange = useCallback(
        (bounds: typeof mapBounds) => updateState({ mapBounds: bounds }),
        [updateState],
    );

    useEffect(() => {
        if (searchMode !== "list") {
            return;
        }

        restoreSearchScrollPosition();
    }, [searchMode]);

    useEffect(() => {
        if (searchMode !== "list") {
            return;
        }

        const controller = new AbortController();

        const timeout = setTimeout(async () => {
            setLoading(true);
            try {
                const params = new URLSearchParams({ q: searchTerm });
                selectedDistances.forEach((d) => params.append("distance", d.toString()));
                selectedVoivodeships.forEach((v) => params.append("voivodeship", v.toString()));
                if (selectedDateRange) {
                    params.append("dateFrom", selectedDateRange.start.toString());
                    params.append("dateTo", selectedDateRange.end.toString());
                }
                params.append("page", page.toString());
                params.append("perPage", PER_PAGE.toString());

                const res = await apiFetch(`/api/search?${params.toString()}`, { signal: controller.signal });
                const data: SearchResponse = await res.json();

                setSearchResponse(data);
                setResults(data.totalHits > 0 ? data.hits : []);
            } catch (err) {
                if (err instanceof Error && err.name !== "AbortError") {
                    console.error("Search failed:", err);
                }
            } finally {
                setLoading(false);
            }
        }, 300);

        return () => {
            clearTimeout(timeout);
            controller.abort();
        };
    }, [searchTerm, selectedDistances, selectedVoivodeships, selectedDateRange, page, searchMode]);

    if (searchMode === "map") {
        return (
            <MapSearchView
                searchTerm={searchTerm}
                selectedDistances={selectedDistances}
                selectedVoivodeships={selectedVoivodeships}
                selectedDateRange={selectedDateRange}
                initialBounds={mapBounds}
                onSearchTermChange={handleSearchTermChange}
                onDistanceChange={handleDistanceChange}
                onVoivodeshipChange={handleVoivodeshipChange}
                onDateChange={handleDateChange}
                onModeChange={handleModeChange}
                onMapBoundsChange={handleMapBoundsChange}
            />
        );
    }

    return (
        <div className="flex flex-col gap-5">
            <SearchBar
                value={searchTerm}
                selectedDistances={selectedDistances}
                selectedVoivodeships={selectedVoivodeships}
                selectedDateRange={selectedDateRange}
                onSearchTermChange={handleSearchTermChange}
                onDistanceChange={handleDistanceChange}
                onVoivodeshipChange={handleVoivodeshipChange}
                onDateChange={handleDateChange}
            />

            <SearchResultsToolbar
                mode="list"
                onModeChange={handleModeChange}
                loading={loading}
                totalHits={searchResponse?.totalHits}
            />

            <Separator />

            {loading ? (
                <ul className="flex flex-col">
                    {Array.from({ length: SKELETON_COUNT }).map((_, i) => (
                        <li key={i}>
                            <SearchResultSkeleton />
                        </li>
                    ))}
                </ul>
            ) : results.length === 0 ? (
                <EmptyState
                    icon="search"
                    title="No races found"
                    description="Try different keywords or remove some filters"
                />
            ) : (
                <ul className="flex flex-col">
                    {results.map((result) => (
                        <li key={result.id} className="list-none">
                            <SearchResult race={result} />
                        </li>
                    ))}
                </ul>
            )}

            {!loading && searchResponse && searchResponse.totalPages > 1 && (
                <SearchPagination
                    currentPage={page}
                    totalPages={searchResponse.totalPages}
                    onPageChange={handlePageChange}
                />
            )}
        </div>
    );
}
