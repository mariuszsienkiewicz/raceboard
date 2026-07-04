import { useCallback, useEffect, useRef, useState } from "react";
import { ArrowRight, Loader2, MapPin } from "lucide-react";
import { apiFetch } from "@/api/client";
import EmptyState from "@/components/EmptyState";
import { Skeleton } from "@/components/ui/skeleton";
import { cn } from "@/lib/utils";
import type { DateRange, FilterKey } from "@/types/search-filters";
import type { MapSearchPoint } from "@/types/search";
import RaceMap, { type MapBounds } from "./RaceMap";
import SearchBar from "./SearchBar";
import SearchModeSwitcher from "./SearchModeSwitcher";
import SearchReturnLink from "./SearchReturnLink";

interface MapSearchViewProps {
    searchTerm: string;
    selectedDistances: Set<FilterKey>;
    selectedVoivodeships: Set<FilterKey>;
    selectedDateRange: DateRange | null;
    initialBounds: MapBounds | null;
    onSearchTermChange: (value: string) => void;
    onDistanceChange: (selected: Set<FilterKey>) => void;
    onVoivodeshipChange: (selected: Set<FilterKey>) => void;
    onDateChange: (selected: DateRange | null) => void;
    onModeChange: (mode: "list" | "map") => void;
    onMapBoundsChange: (bounds: MapBounds) => void;
}

function buildMapSearchParams(
    searchTerm: string,
    selectedDistances: Set<FilterKey>,
    selectedVoivodeships: Set<FilterKey>,
    selectedDateRange: DateRange | null,
    bounds: MapBounds | null,
): URLSearchParams {
    const params = new URLSearchParams({ q: searchTerm });
    selectedDistances.forEach((d) => params.append("distance", d.toString()));
    selectedVoivodeships.forEach((v) => params.append("voivodeship", v.toString()));
    if (selectedDateRange) {
        params.append("dateFrom", selectedDateRange.start.toString());
        params.append("dateTo", selectedDateRange.end.toString());
    }
    if (bounds) {
        params.append("topLat", bounds.topLat.toString());
        params.append("topLng", bounds.topLng.toString());
        params.append("bottomLat", bounds.bottomLat.toString());
        params.append("bottomLng", bounds.bottomLng.toString());
    }
    return params;
}

function MapSearchResult({ point }: { point: MapSearchPoint }) {
    return (
        <SearchReturnLink to={`/races/${point.id}`} className="block">
            <article
                className={cn(
                    "group flex flex-col gap-2 rounded-2xl border border-border bg-card p-4 text-card-foreground",
                    "transition-all duration-200 hover:-translate-y-px hover:shadow-md",
                )}
            >
                <h3 className="flex items-center gap-1.5 text-sm leading-snug font-semibold text-foreground transition-colors group-hover:text-primary">
                    {point.name}
                    <ArrowRight className="size-3.5 shrink-0 -translate-x-1 opacity-0 transition-all duration-200 group-hover:translate-x-0 group-hover:opacity-100" />
                </h3>
                <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                    <MapPin className="size-3.5 shrink-0" />
                    <span>{point.city}</span>
                </div>
            </article>
        </SearchReturnLink>
    );
}

export default function MapSearchView({
    searchTerm,
    selectedDistances,
    selectedVoivodeships,
    selectedDateRange,
    initialBounds,
    onSearchTermChange,
    onDistanceChange,
    onVoivodeshipChange,
    onDateChange,
    onModeChange,
    onMapBoundsChange,
}: MapSearchViewProps) {
    const [points, setPoints] = useState<MapSearchPoint[]>([]);
    const [loading, setLoading] = useState(false);
    const [hasLoadedOnce, setHasLoadedOnce] = useState(false);
    const [bounds, setBounds] = useState<MapBounds | null>(initialBounds);
    const [boundsReady, setBoundsReady] = useState(initialBounds !== null);
    const urlSyncDebounceRef = useRef<ReturnType<typeof setTimeout>>(undefined);
    const fetchGenerationRef = useRef(0);

    const handleBoundsChange = useCallback(
        (newBounds: MapBounds) => {
            setBounds(newBounds);
            setBoundsReady(true);

            clearTimeout(urlSyncDebounceRef.current);
            urlSyncDebounceRef.current = setTimeout(() => {
                onMapBoundsChange(newBounds);
            }, 500);
        },
        [onMapBoundsChange],
    );

    useEffect(() => {
        if (!boundsReady || !bounds) {
            return;
        }

        const generation = ++fetchGenerationRef.current;
        const controller = new AbortController();

        const timeout = setTimeout(async () => {
            setLoading(true);
            try {
                const params = buildMapSearchParams(
                    searchTerm,
                    selectedDistances,
                    selectedVoivodeships,
                    selectedDateRange,
                    bounds,
                );
                const res = await apiFetch(`/api/search/map?${params.toString()}`, {
                    signal: controller.signal,
                });
                const data: MapSearchPoint[] = await res.json();

                if (generation !== fetchGenerationRef.current || controller.signal.aborted) {
                    return;
                }

                setPoints(data.filter((p) => p._geo));
                setHasLoadedOnce(true);
            } catch (err) {
                if (err instanceof Error && err.name !== "AbortError") {
                    console.error("Map search failed:", err);
                }
            } finally {
                if (generation === fetchGenerationRef.current && !controller.signal.aborted) {
                    setLoading(false);
                }
            }
        }, 300);

        return () => {
            clearTimeout(timeout);
            controller.abort();
        };
    }, [searchTerm, selectedDistances, selectedVoivodeships, selectedDateRange, bounds, boundsReady]);

    const showSidebarLoading = !hasLoadedOnce;
    const geoCount = points.length;

    return (
        <div className="fixed inset-x-0 top-16 bottom-0 z-20 flex flex-col bg-background">
            <div className="shrink-0 border-b border-border bg-background/95 px-4 py-3 backdrop-blur-md sm:px-6">
                <div className="mx-auto max-w-[1600px]">
                    <SearchBar
                        variant="compact"
                        value={searchTerm}
                        selectedDistances={selectedDistances}
                        selectedVoivodeships={selectedVoivodeships}
                        selectedDateRange={selectedDateRange}
                        onSearchTermChange={onSearchTermChange}
                        onDistanceChange={onDistanceChange}
                        onVoivodeshipChange={onVoivodeshipChange}
                        onDateChange={onDateChange}
                    />
                </div>
            </div>

            <div className="flex min-h-0 flex-1">
                <aside className="hidden w-[400px] shrink-0 flex-col border-r border-border lg:flex">
                    <div className="shrink-0 border-b border-border px-5 py-4">
                        <p className="text-sm text-muted-foreground">
                            {showSidebarLoading ? (
                                "Searching…"
                            ) : (
                                <>
                                    <span className="font-semibold text-foreground">{geoCount}</span>{" "}
                                    {geoCount === 1 ? "race on map" : "races on map"}
                                </>
                            )}
                        </p>
                    </div>
                    <div className="flex-1 overflow-y-auto px-4 py-3">
                        {showSidebarLoading ? (
                            <div className="flex flex-col gap-2">
                                {Array.from({ length: 8 }).map((_, i) => (
                                    <Skeleton key={i} className="h-20 rounded-2xl" />
                                ))}
                            </div>
                        ) : geoCount === 0 ? (
                            <EmptyState
                                icon="map"
                                title="No races in this area"
                                description="Try zooming out or changing filters"
                            />
                        ) : (
                            <ul className="flex flex-col gap-2">
                                {points.map((point) => (
                                    <li key={point.id} className="list-none">
                                        <MapSearchResult point={point} />
                                    </li>
                                ))}
                            </ul>
                        )}
                    </div>
                </aside>

                <div className="relative min-w-0 flex-1">
                    {loading && hasLoadedOnce && (
                        <div className="absolute inset-0 z-10 flex items-center justify-center bg-background/40 backdrop-blur-[1px]">
                            <Loader2 className="size-8 animate-spin text-muted-foreground" aria-label="Loading map results" />
                        </div>
                    )}
                    <RaceMap
                        points={points}
                        fullHeight
                        restoreBounds={initialBounds}
                        onBoundsChange={handleBoundsChange}
                    />
                    <div className="absolute bottom-6 left-1/2 z-[1000] -translate-x-1/2">
                        <SearchModeSwitcher mode="map" onModeChange={onModeChange} variant="floating" />
                    </div>
                </div>
            </div>
        </div>
    );
}
