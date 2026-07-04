import { useCallback, useMemo } from "react";
import { useSearchParams } from "react-router-dom";
import { parseDate, type CalendarDate } from "@internationalized/date";
import type { MapBounds } from "@/components/search/RaceMap";
import type { DateRange, FilterKey } from "@/types/search-filters";
import type { SearchMode } from "../components/search/SearchModeSwitcher";

export interface SearchState {
    q: string;
    mode: SearchMode;
    distances: Set<FilterKey>;
    voivodeships: Set<FilterKey>;
    dateRange: DateRange | null;
    page: number;
    mapBounds: MapBounds | null;
}

const SCROLL_STORAGE_KEY = "raceboard:searchScroll";

function parseDateRange(dateFrom: string | null, dateTo: string | null): DateRange | null {
    if (!dateFrom || !dateTo) {
        return null;
    }

    try {
        return {
            start: parseDate(dateFrom) as CalendarDate,
            end: parseDate(dateTo) as CalendarDate,
        };
    } catch {
        return null;
    }
}

function parseMapBounds(params: URLSearchParams): MapBounds | null {
    const topLat = params.get("topLat");
    const topLng = params.get("topLng");
    const bottomLat = params.get("bottomLat");
    const bottomLng = params.get("bottomLng");

    if (!topLat || !topLng || !bottomLat || !bottomLng) {
        return null;
    }

    return {
        topLat: Number(topLat),
        topLng: Number(topLng),
        bottomLat: Number(bottomLat),
        bottomLng: Number(bottomLng),
    };
}

export function parseSearchParams(params: URLSearchParams): SearchState {
    const mode = params.get("mode") === "map" ? "map" : "list";

    return {
        q: params.get("q") ?? "",
        mode,
        distances: new Set(params.getAll("distance")),
        voivodeships: new Set(params.getAll("voivodeship")),
        dateRange: parseDateRange(params.get("dateFrom"), params.get("dateTo")),
        page: Math.max(1, Number(params.get("page") ?? "1") || 1),
        mapBounds: mode === "map" ? parseMapBounds(params) : null,
    };
}

export function serializeSearchState(state: SearchState): URLSearchParams {
    const params = new URLSearchParams();

    if (state.q) {
        params.set("q", state.q);
    }
    if (state.mode === "map") {
        params.set("mode", "map");
    }
    state.distances.forEach((d) => params.append("distance", d.toString()));
    state.voivodeships.forEach((v) => params.append("voivodeship", v.toString()));
    if (state.dateRange) {
        params.set("dateFrom", state.dateRange.start.toString());
        params.set("dateTo", state.dateRange.end.toString());
    }
    if (state.page > 1) {
        params.set("page", state.page.toString());
    }
    if (state.mode === "map" && state.mapBounds) {
        params.set("topLat", state.mapBounds.topLat.toString());
        params.set("topLng", state.mapBounds.topLng.toString());
        params.set("bottomLat", state.mapBounds.bottomLat.toString());
        params.set("bottomLng", state.mapBounds.bottomLng.toString());
    }

    return params;
}

export function buildSearchReturnPath(params: URLSearchParams): string {
    const query = params.toString();
    return query ? `/?${query}` : "/";
}

export function saveSearchScrollPosition(): void {
    sessionStorage.setItem(SCROLL_STORAGE_KEY, String(window.scrollY));
}

export function restoreSearchScrollPosition(): void {
    const saved = sessionStorage.getItem(SCROLL_STORAGE_KEY);
    if (!saved) {
        return;
    }

    sessionStorage.removeItem(SCROLL_STORAGE_KEY);
    requestAnimationFrame(() => {
        window.scrollTo(0, Number(saved));
    });
}

export function useSearchState() {
    const [searchParams, setSearchParams] = useSearchParams();
    const state = useMemo(() => parseSearchParams(searchParams), [searchParams]);

    const updateState = useCallback(
        (patch: Partial<SearchState>) => {
            const next: SearchState = { ...state, ...patch };
            setSearchParams(serializeSearchState(next), { replace: true });
        },
        [state, setSearchParams],
    );

    const returnPath = useMemo(() => buildSearchReturnPath(searchParams), [searchParams]);

    return {
        ...state,
        searchParams,
        returnPath,
        updateState,
    };
}
