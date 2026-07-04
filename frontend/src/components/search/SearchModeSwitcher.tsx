import { List, Map } from "lucide-react";
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group";
import { cn } from "@/lib/utils";

export type SearchMode = "list" | "map";

interface SearchModeSwitcherProps {
    mode: SearchMode;
    onModeChange: (mode: SearchMode) => void;
    variant?: "toolbar" | "floating";
}

export default function SearchModeSwitcher({
    mode,
    onModeChange,
    variant = "toolbar",
}: SearchModeSwitcherProps) {
    const isFloating = variant === "floating";

    return (
        <ToggleGroup
            value={[mode]}
            onValueChange={(values) => {
                const next = values[0];
                if (next === "list" || next === "map") {
                    onModeChange(next);
                }
            }}
            variant="outline"
            size="sm"
            spacing={0}
            className={cn(isFloating && "rounded-full shadow-lg ring-1 ring-border")}
        >
            <ToggleGroupItem
                value="list"
                aria-label="List view"
                className={cn(
                    "gap-1.5 data-[state=on]:bg-primary/10 data-[state=on]:text-primary",
                    isFloating && "rounded-l-full px-4",
                )}
            >
                <List className="size-4" />
                <span className="text-sm font-medium">List</span>
            </ToggleGroupItem>
            <ToggleGroupItem
                value="map"
                aria-label="Map view"
                className={cn(
                    "gap-1.5 data-[state=on]:bg-primary/10 data-[state=on]:text-primary",
                    isFloating && "rounded-r-full px-4",
                )}
            >
                <Map className="size-4" />
                <span className="text-sm font-medium">Map</span>
            </ToggleGroupItem>
        </ToggleGroup>
    );
}
