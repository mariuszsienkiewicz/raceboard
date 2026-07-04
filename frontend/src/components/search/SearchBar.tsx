import type { DateRange, FilterKey } from "@/types/search-filters";
import { Search, X } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Separator } from "@/components/ui/separator";
import { cn } from "@/lib/utils";
import DistanceTags from "./DistanceTags";
import { VoivodeshipSelect } from "./VoivodeshipSelect";
import { DateFilter } from "./DateFilter";

interface SearchBarProps {
    variant?: "default" | "compact";
    value?: string;
    selectedDistances?: Set<FilterKey>;
    selectedVoivodeships?: Set<FilterKey>;
    selectedDateRange?: DateRange | null;
    onSearchTermChange?: (value: string) => void;
    onDistanceChange?: (selected: Set<FilterKey>) => void;
    onVoivodeshipChange?: (selected: Set<FilterKey>) => void;
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
    const hasValue = Boolean(value);

    return (
        <div
            className={cn(
                "flex flex-col border border-border bg-card text-card-foreground shadow-sm",
                isCompact ? "gap-3 rounded-2xl px-4 py-3" : "gap-5 rounded-3xl px-6 py-5",
            )}
        >
            <div className="relative">
                <Label htmlFor="search-races" className="sr-only">
                    Search races
                </Label>
                <Search
                    aria-hidden
                    className="pointer-events-none absolute top-1/2 left-3 size-5 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    id="search-races"
                    type="search"
                    placeholder="Search races, cities…"
                    value={value ?? ""}
                    onChange={(e) => onSearchTermChange?.(e.target.value)}
                    className={cn(
                        "bg-secondary text-base",
                        isCompact ? "h-10" : "h-12",
                        "pl-10",
                        hasValue && "pr-10",
                    )}
                />
                {hasValue && (
                    <Button
                        type="button"
                        variant="ghost"
                        size="icon-xs"
                        className="absolute top-1/2 right-2 -translate-y-1/2 text-muted-foreground"
                        aria-label="Clear search"
                        onClick={() => onSearchTermChange?.("")}
                    >
                        <X />
                    </Button>
                )}
            </div>
            <div className="flex flex-wrap items-center gap-4">
                <DistanceTags selectedKeys={selectedDistances} onChange={onDistanceChange} />
                <Separator orientation="vertical" className="hidden sm:block" />
                <VoivodeshipSelect selectedKeys={selectedVoivodeships} onChange={onVoivodeshipChange} />
                <Separator orientation="vertical" className="hidden sm:block" />
                <DateFilter value={selectedDateRange} onChange={onDateChange} />
            </div>
        </div>
    );
}
