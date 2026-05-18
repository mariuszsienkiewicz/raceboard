import { DateField, DateRangePicker, Label, RangeCalendar } from "@heroui/react";

export function DateFilter() {
    return (
        <DateRangePicker className="w-72" endName="endDate" startName="startDate">
            <Label className="text-xs font-medium text-muted uppercase tracking-wide mb-1.5 block">
                Date
            </Label>
            <DateField.Group fullWidth>
                <DateField.Input slot="start">
                    {(segment) => <DateField.Segment segment={segment} />}
                </DateField.Input>
                <DateRangePicker.RangeSeparator />
                <DateField.Input slot="end">
                    {(segment) => <DateField.Segment segment={segment} />}
                </DateField.Input>
                <DateField.Suffix>
                    <DateRangePicker.Trigger>
                        <DateRangePicker.TriggerIndicator />
                    </DateRangePicker.Trigger>
                </DateField.Suffix>
            </DateField.Group>
            <DateRangePicker.Popover>
                <RangeCalendar aria-label="Trip dates">
                    <RangeCalendar.Header>
                        <RangeCalendar.YearPickerTrigger>
                            <RangeCalendar.YearPickerTriggerHeading />
                            <RangeCalendar.YearPickerTriggerIndicator />
                        </RangeCalendar.YearPickerTrigger>
                        <RangeCalendar.NavButton slot="previous" />
                        <RangeCalendar.NavButton slot="next" />
                    </RangeCalendar.Header>
                    <RangeCalendar.Grid>
                        <RangeCalendar.GridHeader>
                            {(day) => <RangeCalendar.HeaderCell>{day}</RangeCalendar.HeaderCell>}
                        </RangeCalendar.GridHeader>
                        <RangeCalendar.GridBody>
                            {(date) => <RangeCalendar.Cell date={date} />}
                        </RangeCalendar.GridBody>
                    </RangeCalendar.Grid>
                    <RangeCalendar.YearPickerGrid>
                        <RangeCalendar.YearPickerGridBody>
                            {({ year }) => <RangeCalendar.YearPickerCell year={year} />}
                        </RangeCalendar.YearPickerGridBody>
                    </RangeCalendar.YearPickerGrid>
                </RangeCalendar>
            </DateRangePicker.Popover>
        </DateRangePicker>
    );
}
