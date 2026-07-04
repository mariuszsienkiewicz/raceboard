import type { FilterKey } from "@/types/search-filters";
import { Label } from "@/components/ui/label";
import { ToggleGroup, ToggleGroupItem } from "@/components/ui/toggle-group";

const DISTANCES = [
    { value: "5", label: "5K" },
    { value: "10", label: "10K" },
    { value: "21", label: "Half" },
    { value: "42", label: "Marathon" },
] as const;

interface DistanceTagsProps {
    selectedKeys?: Set<FilterKey>;
    onChange?: (selected: Set<FilterKey>) => void;
}

function toValueArray(keys?: Set<FilterKey>): string[] {
    if (!keys) {
        return [];
    }

    return Array.from(keys, (key) => String(key));
}

export default function DistanceTags({ selectedKeys, onChange }: DistanceTagsProps) {
    return (
        <div>
            <Label className="mb-1.5 block text-xs font-medium tracking-wide text-muted-foreground uppercase">
                Distance
            </Label>
            <ToggleGroup
                multiple
                variant="outline"
                size="sm"
                aria-label="Filter by distance"
                value={toValueArray(selectedKeys)}
                onValueChange={(values) => onChange?.(new Set(values))}
                className="flex flex-wrap gap-1.5"
            >
                {DISTANCES.map(({ value, label }) => (
                    <ToggleGroupItem key={value} value={value}>
                        {label}
                    </ToggleGroupItem>
                ))}
            </ToggleGroup>
        </div>
    );
}
