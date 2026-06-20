import { FieldError, Label, SearchField, Separator, type DateRange, type Key } from "@heroui/react";
import { Surface } from "@heroui/react/surface";
import DistanceTags from "./DistanceTags";
import { VoivodeshipSelect } from "./VoivodeshipSelect";
import { DateFilter } from "./DateFilter";

interface SearchBarProps {
    value?: string;
    onSearchTermChange?: (value: string) => void;
    onDistanceChange?: (selected: Set<Key>) => void;
    onVoivodeshipChange?: (selected: Set<Key>) => void;
    onDateChange?: (selected: DateRange | null) => void;
    onViewModeChange?: (viewMode: "list" | "map") => void;
}

export default function SearchBar({ value, onSearchTermChange, onDistanceChange, onVoivodeshipChange, onDateChange }: SearchBarProps) {
    return (
        <Surface className="flex flex-col gap-5 rounded-3xl px-6 py-5 shadow-sm">
            <SearchField variant="secondary">
                <Label className="sr-only">Search races</Label>
                <SearchField.Group className="h-12">
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
                <DistanceTags onChange={onDistanceChange} />
                <Separator orientation="vertical" className="hidden sm:block" />
                <VoivodeshipSelect onChange={onVoivodeshipChange} />
                <Separator orientation="vertical" className="hidden sm:block" />
                <DateFilter onChange={onDateChange} />
            </div>
        </Surface>
    );
}