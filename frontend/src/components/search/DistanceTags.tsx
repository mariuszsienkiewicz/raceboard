import { Label, Tag, TagGroup, type Key, type Selection } from "@heroui/react";

interface DistanceTagsProps {
    selectedKeys?: Set<Key>;
    onChange?: (selected: Set<Key>) => void;
}

export default function DistanceTags({ selectedKeys, onChange }: DistanceTagsProps) {
    function handleSelectionChange(selection: Selection) {
        if (selection === "all") return;
        onChange?.(selection);
    }

    return (
        <TagGroup
            aria-label="Filter by distance"
            selectionMode="multiple"
            selectedKeys={selectedKeys}
            onSelectionChange={handleSelectionChange}
        >
            <Label className="text-xs font-medium text-muted uppercase tracking-wide mb-1.5 block">
                Distance
            </Label>
            <TagGroup.List className="flex gap-1.5">
                <Tag id="5">5K</Tag>
                <Tag id="10">10K</Tag>
                <Tag id="21">Half</Tag>
                <Tag id="42">Marathon</Tag>
            </TagGroup.List>
        </TagGroup>
    );
}
