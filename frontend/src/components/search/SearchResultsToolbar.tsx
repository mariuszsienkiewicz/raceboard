import SearchModeSwitcher, { type SearchMode } from "./SearchModeSwitcher";
import { cn } from "@/lib/utils";

interface SearchResultsToolbarProps {
    mode: SearchMode;
    onModeChange: (mode: SearchMode) => void;
    loading?: boolean;
    totalHits?: number;
    mapCount?: number;
}

export default function SearchResultsToolbar({
    mode,
    onModeChange,
    loading = false,
    totalHits,
    mapCount,
}: SearchResultsToolbarProps) {
    const label = (() => {
        if (loading) {
            return "Searching…";
        }
        if (mode === "map" && mapCount !== undefined) {
            return (
                <>
                    <span className="font-semibold text-foreground">{mapCount}</span>{" "}
                    {mapCount === 1 ? "race on map" : "races on map"}
                </>
            );
        }
        if (totalHits !== undefined && totalHits > 0) {
            return (
                <>
                    <span className="font-semibold text-foreground">{totalHits}</span>{" "}
                    {totalHits === 1 ? "race found" : "races found"}
                </>
            );
        }
        return "Explore races across Poland";
    })();

    return (
        <div
            className={cn(
                "flex items-center justify-between gap-4 rounded-2xl border border-border bg-card px-4 py-3 text-card-foreground shadow-sm",
            )}
        >
            <p className="text-sm text-muted-foreground">{label}</p>
            <SearchModeSwitcher mode={mode} onModeChange={onModeChange} />
        </div>
    );
}
