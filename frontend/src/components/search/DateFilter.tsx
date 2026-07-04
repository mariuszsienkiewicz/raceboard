import { useState, type KeyboardEvent, type MouseEvent } from "react";
import { CalendarDate } from "@internationalized/date";
import type { DateRange } from "@/types/search-filters";
import { format } from "date-fns";
import { pl } from "date-fns/locale";
import { CalendarIcon, X } from "lucide-react";
import type { DateRange as DayPickerDateRange } from "react-day-picker";
import { Calendar } from "@/components/ui/calendar";
import { Label } from "@/components/ui/label";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { cn } from "@/lib/utils";

interface DateFilterProps {
    value?: DateRange | null;
    onChange?: (selected: DateRange | null) => void;
}

function toDayPickerRange(value: DateRange | null | undefined): DayPickerDateRange | undefined {
    if (!value) {
        return undefined;
    }

    return {
        from: new Date(value.start.year, value.start.month - 1, value.start.day),
        to: new Date(value.end.year, value.end.month - 1, value.end.day),
    };
}

function toDateRange(range: DayPickerDateRange | undefined): DateRange | null {
    if (!range?.from) {
        return null;
    }

    const endDate = range.to ?? range.from;

    return {
        start: new CalendarDate(range.from.getFullYear(), range.from.getMonth() + 1, range.from.getDate()),
        end: new CalendarDate(endDate.getFullYear(), endDate.getMonth() + 1, endDate.getDate()),
    };
}

function formatDateRange(value: DateRange): string {
    const from = new Date(value.start.year, value.start.month - 1, value.start.day);
    const to = new Date(value.end.year, value.end.month - 1, value.end.day);

    if (value.start.toString() === value.end.toString()) {
        return format(from, "d MMM yyyy", { locale: pl });
    }

    return `${format(from, "d MMM yyyy", { locale: pl })} – ${format(to, "d MMM yyyy", { locale: pl })}`;
}

export function DateFilter({ value, onChange }: DateFilterProps) {
    const [open, setOpen] = useState(false);
    const [draftRange, setDraftRange] = useState<DayPickerDateRange | undefined>(toDayPickerRange(value));

    function handleOpenChange(nextOpen: boolean) {
        if (nextOpen) {
            setDraftRange(toDayPickerRange(value));
        }

        setOpen(nextOpen);
    }

    function handleSelect(range: DayPickerDateRange | undefined) {
        setDraftRange(range);

        if (range?.from && range?.to) {
            onChange?.(toDateRange(range));
            setOpen(false);
        }
    }

    function clearRange(event: MouseEvent) {
        event.preventDefault();
        event.stopPropagation();
        setDraftRange(undefined);
        onChange?.(null);
    }

    function handleClearKeyDown(event: KeyboardEvent) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            clearRange(event as unknown as MouseEvent);
        }
    }

    return (
        <div className="w-72">
            <Label className="mb-1.5 block text-xs font-medium tracking-wide text-muted-foreground uppercase">
                Date
            </Label>
            <Popover open={open} onOpenChange={handleOpenChange}>
                <PopoverTrigger
                    className={cn(
                        "inline-flex h-9 w-full items-center justify-between gap-2 rounded-3xl border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none transition-colors",
                        "hover:bg-muted/50 focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/30",
                        !value && "text-muted-foreground",
                    )}
                >
                    <span className="flex min-w-0 items-center gap-2">
                        <CalendarIcon className="size-4 shrink-0" />
                        <span className="truncate">{value ? formatDateRange(value) : "Pick a date range"}</span>
                    </span>
                    {value && (
                        <span
                            role="button"
                            tabIndex={0}
                            aria-label="Clear date range"
                            className="inline-flex size-6 shrink-0 cursor-pointer items-center justify-center rounded-full text-muted-foreground hover:text-foreground"
                            onClick={clearRange}
                            onKeyDown={handleClearKeyDown}
                        >
                            <X className="size-3.5" />
                        </span>
                    )}
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                        mode="range"
                        defaultMonth={draftRange?.from ?? toDayPickerRange(value)?.from}
                        selected={draftRange}
                        onSelect={handleSelect}
                        numberOfMonths={1}
                    />
                </PopoverContent>
            </Popover>
        </div>
    );
}
