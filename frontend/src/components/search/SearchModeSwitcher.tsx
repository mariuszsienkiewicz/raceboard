import { ListBulletIcon, MapIcon } from "@heroicons/react/24/outline";
import { Button, ButtonGroup } from "@heroui/react";

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
        <ButtonGroup
            variant="tertiary"
            className={isFloating ? "rounded-full shadow-lg ring-1 ring-border" : "rounded-xl"}
        >
            <Button
                aria-label="List view"
                aria-pressed={mode === "list"}
                className={`gap-1.5 ${isFloating ? "rounded-full px-4" : "px-3"} ${
                    mode === "list" ? "bg-primary/10 text-primary" : ""
                }`}
                onClick={() => onModeChange("list")}
            >
                <ListBulletIcon className="size-4" />
                <span className="text-sm font-medium">List</span>
            </Button>
            <Button
                aria-label="Map view"
                aria-pressed={mode === "map"}
                className={`gap-1.5 ${isFloating ? "rounded-full px-4" : "px-3"} ${
                    mode === "map" ? "bg-primary/10 text-primary" : ""
                }`}
                onClick={() => onModeChange("map")}
            >
                <ButtonGroup.Separator />
                <MapIcon className="size-4" />
                <span className="text-sm font-medium">Map</span>
            </Button>
        </ButtonGroup>
    );
}
