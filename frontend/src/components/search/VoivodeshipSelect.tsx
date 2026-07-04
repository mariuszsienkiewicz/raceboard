import type { Key } from "@heroui/react";
import {
    Autocomplete,
    EmptyState,
    Label,
    ListBox,
    SearchField,
    Tag,
    TagGroup,
    useFilter,
} from "@heroui/react";

interface VoivodeshipSelectProps {
    selectedKeys?: Set<Key>;
    onChange?: (selected: Set<Key>) => void;
}

export function VoivodeshipSelect({ selectedKeys = new Set(), onChange }: VoivodeshipSelectProps) {
    const { contains } = useFilter({ sensitivity: "base" });
    const items = [
        { id: "dolnośląskie", name: "dolnośląskie" },
        { id: "kujawsko-pomorskie", name: "kujawsko-pomorskie" },
        { id: "lubelskie", name: "lubelskie" },
        { id: "lubuskie", name: "lubuskie" },
        { id: "łódzkie", name: "łódzkie" },
        { id: "małopolskie", name: "małopolskie" },
        { id: "mazowieckie", name: "mazowieckie" },
        { id: "opolskie", name: "opolskie" },
        { id: "podkarpackie", name: "podkarpackie" },
        { id: "podlaskie", name: "podlaskie" },
        { id: "pomorskie", name: "pomorskie" },
        { id: "śląskie", name: "śląskie" },
        { id: "świętokrzyskie", name: "świętokrzyskie" },
        { id: "warmińsko-mazurskie", name: "warmińsko-mazurskie" },
        { id: "wielkopolskie", name: "wielkopolskie" },
        { id: "zachodniopomorskie", name: "zachodniopomorskie" },
    ];

    const onRemoveTags = (keys: Set<Key>) => {
        const newKeys = new Set([...selectedKeys].filter((key) => !keys.has(key)));
        onChange?.(newKeys);
    };

    const handleChange = (value: Key | Key[] | null) => {
        const newKeys = new Set(Array.isArray(value) ? value : value != null ? [value] : []);
        onChange?.(newKeys);
    };

    return (
        <Autocomplete
            className="w-[256px]"
            placeholder="Select voivodeship"
            selectionMode="multiple"
            value={[...selectedKeys]}
            onChange={handleChange}
        >
            <Label className="text-xs font-medium text-muted uppercase tracking-wide mb-1.5 block">
                Voivodeship
            </Label>
            <Autocomplete.Trigger>
                <Autocomplete.Value>
                    {({ defaultChildren, isPlaceholder, state }) => {
                        if (isPlaceholder || state.selectedItems.length === 0) {
                            return defaultChildren;
                        }
                        const selectedItemsKeys = state.selectedItems.map((item) => item.key);
                        return (
                            <TagGroup size="sm" onRemove={onRemoveTags} aria-label="Selected voivodeship">
                                <TagGroup.List>
                                    {selectedItemsKeys.map((selectedItemKey) => {
                                        const item = items.find((s) => s.id === selectedItemKey);
                                        if (!item) return null;
                                        return (
                                            <Tag key={item.id} id={item.id}>
                                                {item.name}
                                            </Tag>
                                        );
                                    })}
                                </TagGroup.List>
                            </TagGroup>
                        );
                    }}
                </Autocomplete.Value>
                <Autocomplete.ClearButton />
                <Autocomplete.Indicator />
            </Autocomplete.Trigger>
            <Autocomplete.Popover>
                <Autocomplete.Filter filter={contains}>
                    <SearchField autoFocus name="search" variant="secondary" aria-label="Search voivodeship">
                        <SearchField.Group>
                            <SearchField.SearchIcon />
                            <SearchField.Input placeholder="Search..." />
                            <SearchField.ClearButton />
                        </SearchField.Group>
                    </SearchField>
                    <ListBox renderEmptyState={() => <EmptyState>No results found</EmptyState>}>
                        {items.map((item) => (
                            <ListBox.Item key={item.id} id={item.id} textValue={item.name}>
                                {item.name}
                                <ListBox.ItemIndicator />
                            </ListBox.Item>
                        ))}
                    </ListBox>
                </Autocomplete.Filter>
            </Autocomplete.Popover>
        </Autocomplete>
    )
}