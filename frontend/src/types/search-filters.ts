import type { CalendarDate } from "@internationalized/date";

export type FilterKey = string;

export interface DateRange {
    start: CalendarDate;
    end: CalendarDate;
}
