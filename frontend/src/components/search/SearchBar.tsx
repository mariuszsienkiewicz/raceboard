import { FieldError, Label, SearchField, Separator, type DateRange, type Key } from "@heroui/react";
import { Surface } from "@heroui/react/surface";
import DistanceTags from "./DistanceTags";
import { VoivodeshipSelect } from "./VoivodeshipSelect";
import { DateFilter } from "./DateFilter";

interface SearchBarProps {
    variant?: "default" | "compact";
    value?: string;
    selectedDistances?: Set<Key>;
    selectedVoivodeships?: Set<Key>;
    selectedDateRange?: DateRange | null;
    onSearchTermChange?: (value: string) => void;
    onDistanceChange?: (selected: Set<Key>) => void;
    onVoivodeshipChange?: (selected: Set<Key>) => void;
    onDateChange?: (selected: DateRange | null) => void;
}

export default function SearchBar({
    variant = "default",
    value,
    selectedDistances,
    selectedVoivodeships,
    selectedDateRange,
    onSearchTermChange,
    onDistanceChange,
    onVoivodeshipChange,
    onDateChange,
}: SearchBarProps) {
    const isCompact = variant === "compact";

    return (
        <Surface
            className={`flex flex-col shadow-sm ${isCompact ? "gap-3 rounded-2xl px-4 py-3" : "gap-5 rounded-3xl px-6 py-5"}`}
        >
            <SearchField variant="secondary">
                <Label className="sr-only">Search races</Label>
                <SearchField.Group className={isCompact ? "h-10" : "h-12"}>
                    <SearchField.SearchIcon className="size-5 text-muted" />
                    <SearchField.Input
                        placeholder="Search races, cities…"
                        value={value}
                        onChange={(e) => onSearchTermChange?.(e.target.value)}
                        className="text-base"
                    />
                    <SearchField.ClearButton />
                </SearchField.Group>
                <FieldError />
            </SearchField>
            <div className="flex flex-wrap items-center gap-4">
                <DistanceTags selectedKeys={selectedDistances} onChange={onDistanceChange} />
                <Separator orientation="vertical" className="hidden sm:block" />
                <VoivodeshipSelect selectedKeys={selectedVoivodeships} onChange={onVoivodeshipChange} />
                <Separator orientation="vertical" className="hidden sm:block" />
                <DateFilter value={selectedDateRange} onChange={onDateChange} />
            </div>
        </Surface>
    );
}