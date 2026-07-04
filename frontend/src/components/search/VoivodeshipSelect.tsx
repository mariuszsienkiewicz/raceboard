import { useState, type KeyboardEvent, type MouseEvent } from "react";
import type { FilterKey } from "@/types/search-filters";
import { Check, ChevronsUpDown, X } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from "@/components/ui/command";
import { Label } from "@/components/ui/label";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { cn } from "@/lib/utils";

const VOIVODESHIPS = [
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
] as const;

interface VoivodeshipSelectProps {
    selectedKeys?: Set<FilterKey>;
    onChange?: (selected: Set<FilterKey>) => void;
}

export function VoivodeshipSelect({ selectedKeys = new Set(), onChange }: VoivodeshipSelectProps) {
    const [open, setOpen] = useState(false);

    function toggleVoivodeship(id: string) {
        const next = new Set(selectedKeys);

        if (next.has(id)) {
            next.delete(id);
        } else {
            next.add(id);
        }

        onChange?.(next);
    }

    function removeVoivodeship(id: string, event: MouseEvent) {
        event.preventDefault();
        event.stopPropagation();

        const next = new Set(selectedKeys);
        next.delete(id);
        onChange?.(next);
    }

    function clearAll(event: MouseEvent) {
        event.preventDefault();
        event.stopPropagation();
        onChange?.(new Set());
    }

    function handleRemoveKeyDown(id: string, event: KeyboardEvent) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            removeVoivodeship(id, event as unknown as MouseEvent);
        }
    }

    function handleClearKeyDown(event: KeyboardEvent) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            clearAll(event as unknown as MouseEvent);
        }
    }

    return (
        <div className="w-[256px]">
            <Label className="mb-1.5 block text-xs font-medium tracking-wide text-muted-foreground uppercase">
                Voivodeship
            </Label>
            <Popover open={open} onOpenChange={setOpen}>
                <PopoverTrigger
                    className={cn(
                        "flex min-h-9 w-full items-center justify-between gap-2 rounded-3xl border border-input bg-background px-3 py-1.5 text-sm shadow-xs outline-none transition-colors",
                        "hover:bg-muted/50 focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/30",
                    )}
                >
                    <span className="flex min-w-0 flex-1 flex-wrap items-center gap-1">
                        {selectedKeys.size === 0 ? (
                            <span className="text-muted-foreground">Select voivodeship</span>
                        ) : (
                            Array.from(selectedKeys).map((key) => {
                                const id = String(key);
                                const item = VOIVODESHIPS.find((voivodeship) => voivodeship.id === id);

                                return (
                                    <Badge key={id} variant="secondary" className="gap-0.5 pr-1">
                                        {item?.name ?? id}
                                        <span
                                            role="button"
                                            tabIndex={0}
                                            aria-label={`Remove ${item?.name ?? id}`}
                                            className="inline-flex size-4 cursor-pointer items-center justify-center rounded-full text-muted-foreground hover:text-foreground"
                                            onClick={(event) => removeVoivodeship(id, event)}
                                            onKeyDown={(event) => handleRemoveKeyDown(id, event)}
                                        >
                                            <X className="size-3" />
                                        </span>
                                    </Badge>
                                );
                            })
                        )}
                    </span>
                    <span className="flex shrink-0 items-center gap-0.5">
                        {selectedKeys.size > 0 && (
                            <span
                                role="button"
                                tabIndex={0}
                                aria-label="Clear selected voivodeships"
                                className="inline-flex size-6 cursor-pointer items-center justify-center rounded-full text-muted-foreground hover:text-foreground"
                                onClick={clearAll}
                                onKeyDown={handleClearKeyDown}
                            >
                                <X className="size-3.5" />
                            </span>
                        )}
                        <ChevronsUpDown className="size-4 shrink-0 text-muted-foreground" />
                    </span>
                </PopoverTrigger>
                <PopoverContent className="w-[256px] p-0" align="start">
                    <Command>
                        <CommandInput placeholder="Search..." />
                        <CommandList>
                            <CommandEmpty>No results found</CommandEmpty>
                            <CommandGroup>
                                {VOIVODESHIPS.map((item) => (
                                    <CommandItem
                                        key={item.id}
                                        value={item.name}
                                        onSelect={() => toggleVoivodeship(item.id)}
                                    >
                                        <Check
                                            className={cn(
                                                "size-4",
                                                selectedKeys.has(item.id) ? "opacity-100" : "opacity-0",
                                            )}
                                        />
                                        {item.name}
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </CommandList>
                    </Command>
                </PopoverContent>
            </Popover>
        </div>
    );
}
