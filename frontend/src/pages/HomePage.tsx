import { CalendarCheck, Footprints, MapPinned, Sparkles } from "lucide-react";
import { Link, useSearchParams } from "react-router-dom";
import Search from "@/components/search/Search";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";

const POPULAR_DISTANCES = [
    { label: "5K races", to: "/?distance=5" },
    { label: "10K races", to: "/?distance=10" },
    { label: "Half marathons", to: "/?distance=21" },
    { label: "Marathons", to: "/?distance=42" },
] as const;

const POPULAR_REGIONS = [
    { label: "mazowieckie", to: "/?voivodeship=mazowieckie" },
    { label: "małopolskie", to: "/?voivodeship=ma%C5%82opolskie" },
    { label: "dolnośląskie", to: "/?voivodeship=dolno%C5%9Bl%C4%85skie" },
    { label: "pomorskie", to: "/?voivodeship=pomorskie" },
    { label: "wielkopolskie", to: "/?voivodeship=wielkopolskie" },
    { label: "śląskie", to: "/?voivodeship=%C5%9Bl%C4%85skie" },
] as const;

const HIGHLIGHTS = [
    {
        icon: CalendarCheck,
        title: "Always up to date",
        description:
            "New editions and freshly announced races are imported automatically, so the calendar stays current without you refreshing a dozen tabs.",
    },
    {
        icon: MapPinned,
        title: "Search the whole country",
        description:
            "Filter by city, voivodeship and distance, or switch to the map to see exactly where each start line is.",
    },
    {
        icon: Sparkles,
        title: "Clean, merged data",
        description:
            "Duplicates from different calendars are detected and combined, so one race means one clear listing.",
    },
] as const;

function QuickLinks({ title, links }: { title: string; links: readonly { label: string; to: string }[] }) {
    return (
        <div className="flex flex-col gap-3">
            <span className="text-xs font-semibold tracking-widest text-muted-foreground uppercase">
                {title}
            </span>
            <div className="flex flex-wrap gap-2">
                {links.map(({ label, to }) => (
                    <Badge
                        key={to}
                        variant="outline"
                        className="h-8 px-3.5 text-sm font-medium"
                        render={<Link to={to} />}
                    >
                        {label}
                    </Badge>
                ))}
            </div>
        </div>
    );
}

export default function HomePage() {
    const [searchParams] = useSearchParams();
    const isMapMode = searchParams.get("mode") === "map";

    return (
        <div className={cn(!isMapMode && "flex flex-col gap-10 sm:gap-12")}>
            {!isMapMode && (
                <section className="relative px-2 pt-6 pb-1 text-center sm:pt-10">
                    <div
                        aria-hidden
                        className="pointer-events-none absolute inset-x-0 -top-6 mx-auto h-56 max-w-2xl rounded-full bg-[radial-gradient(ellipse_at_center,var(--color-muted)_0%,transparent_70%)] opacity-70 blur-2xl"
                    />
                    <div className="relative mx-auto flex max-w-2xl flex-col items-center gap-5">
                        <Badge
                            variant="outline"
                            className="h-7 gap-1.5 border-border/80 bg-background/80 px-3.5 py-1 text-xs font-medium tracking-wide text-muted-foreground uppercase backdrop-blur-sm"
                        >
                            <Footprints className="size-3.5" strokeWidth={1.75} />
                            Poland&apos;s running calendar
                        </Badge>

                        <div className="space-y-3">
                            <h1 className="text-4xl font-bold tracking-tight text-balance text-foreground sm:text-5xl">
                                Find your next race
                            </h1>
                            <p className="mx-auto max-w-lg text-base leading-relaxed text-pretty text-muted-foreground">
                                Discover hundreds of running events across Poland, from local 5Ks to full
                                marathons, all gathered into one searchable calendar.
                            </p>
                        </div>
                    </div>
                </section>
            )}

            <Search />

            {!isMapMode && (
                <>
                    <section className="flex flex-col gap-6 sm:flex-row sm:gap-10">
                        <QuickLinks title="Popular distances" links={POPULAR_DISTANCES} />
                        <QuickLinks title="Popular regions" links={POPULAR_REGIONS} />
                    </section>

                    <section className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        {HIGHLIGHTS.map((item) => (
                            <div
                                key={item.title}
                                className="flex flex-col gap-3 rounded-2xl border border-border bg-card p-5 ring-1 ring-foreground/5"
                            >
                                <span className="flex size-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                    <item.icon className="size-5" strokeWidth={1.75} />
                                </span>
                                <div className="flex flex-col gap-1.5">
                                    <h3 className="text-base font-semibold text-foreground">{item.title}</h3>
                                    <p className="text-sm leading-relaxed text-muted-foreground">
                                        {item.description}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </section>

                    <section className="flex flex-col gap-4 border-t border-border pt-10">
                        <h2 className="text-2xl font-bold tracking-tight text-foreground">
                            Running races in Poland, all in one place
                        </h2>
                        <div className="grid grid-cols-1 gap-6 text-base leading-relaxed text-muted-foreground lg:grid-cols-2">
                            <p>
                                Raceboard is a running calendar that brings together events from across
                                Poland into a single, searchable list. Whether you are chasing your first 5K,
                                hunting for a fast 10K, building up to a half marathon or targeting a full
                                marathon, you can find upcoming races, check their dates and see the distances
                                on offer in seconds.
                            </p>
                            <p>
                                Events are collected from trusted Polish calendars, then normalized and
                                deduplicated so you get clean, reliable information. Browse by city or region,
                                narrow things down by distance, open the map to see where each race starts and
                                save the ones you like to your watchlist so you never miss a start line.
                            </p>
                        </div>
                    </section>
                </>
            )}
        </div>
    );
}
