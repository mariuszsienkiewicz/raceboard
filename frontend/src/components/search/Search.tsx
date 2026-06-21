import { Separator, type DateRange, type Key } from "@heroui/react";
import SearchBar from "./SearchBar";
import { useCallback, useEffect, useState } from "react";
import { apiFetch } from "../../api/client";
import SearchResult, { SearchResultSkeleton } from "./SearchResult";
import type { Race } from "../../types/race";
import type { SearchResponse } from "../../types/search";
import SearchPagination from "./SearchPagination";
import RaceMap from "./RaceMap";
import ResultViewModeSwitcher from "./ResultViewModeSwitcher";

const SKELETON_COUNT = 5;

function totalPages(totalHits: number, pageSize: number): number {
    return Math.max(1, Math.ceil(totalHits / pageSize));
}

export default function Search() {
    const [searchTerm, setSearchTerm] = useState("");
    const [selectedDistances, setSelectedDistances] = useState<Set<Key>>(new Set());
    const [selectedVoivodeships, setSelectedVoivodeships] = useState<Set<Key>>(new Set());
    const [selectedDateRange, setSelectedDateRange] = useState<DateRange | null>(null);
    const [searchResponse, setSearchResponse] = useState<SearchResponse | null>(null);
    const [page, setPage] = useState(1);
    const [perPage] = useState(20);
    const [results, setResults] = useState<Race[]>([]);
    const [loading, setLoading] = useState(true);
    const [viewMode, setViewMode] = useState<"list" | "map">("list");

    const handleSearchTermChange = useCallback((value: string) => {
        setSearchTerm(value);
        setPage(1);
    }, []);

    const handleDistanceChange = useCallback((selected: Set<Key>) => {
        setSelectedDistances(selected);
        setPage(1);
    }, []);

    const handleVoivodeshipChange = useCallback((selected: Set<Key>) => {
        setSelectedVoivodeships(selected);
        setPage(1);
    }, []);

    const handleDateChange = useCallback((selected: DateRange | null) => {
        setSelectedDateRange(selected);
        setPage(1);
    }, []);

    useEffect(() => {
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
                params.append("perPage", perPage.toString());

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
    }, [searchTerm, selectedDistances, selectedVoivodeships, selectedDateRange, page, perPage]);

    return (
        <div className="flex flex-col gap-6">
            <SearchBar
                value={searchTerm}
                onSearchTermChange={handleSearchTermChange}
                onDistanceChange={handleDistanceChange}
                onVoivodeshipChange={handleVoivodeshipChange}
                onDateChange={handleDateChange}
                onViewModeChange={setViewMode}
            />

            {!loading && searchResponse && searchResponse.totalHits > 0 && (
                <div className="flex items-center justify-between">
                    <p className="text-sm text-muted px-1">
                        <span className="font-semibold text-foreground">{searchResponse.totalHits}</span>{" "}
                        {searchResponse.totalHits === 1 ? "race found" : "races found"}
                    </p>
                    <ResultViewModeSwitcher onViewModeChange={setViewMode} />
                </div>
            )}

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
                <div className="flex flex-col items-center gap-3 py-20 text-center">
                    <span className="text-5xl">🔍</span>
                    <p className="font-semibold text-foreground">No races found</p>
                    <p className="text-sm text-muted">Try different keywords or remove some filters</p>
                </div>
            ) : (
                (viewMode === "list" ? (
                    <ul className="flex flex-col">
                        {results.map((result) => (
                            <li key={result.id} className="list-none">
                                <SearchResult race={result} />
                            </li>
                        ))}
                    </ul>
                ) : (
                    <RaceMap races={results} />
                ))
            )}

            <SearchPagination
                onPageChange={setPage}
                totalPages={totalPages(searchResponse?.totalHits ?? 0, searchResponse?.perPage ?? perPage)}
            />
        </div>
    );
}