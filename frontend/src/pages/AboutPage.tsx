import { Link } from "react-router-dom";
import {
    ArrowRight,
    CalendarSearch,
    Database,
    Footprints,
    GitMerge,
    Globe,
    ListChecks,
    MapPin,
    Search,
    ShieldCheck,
    Sparkles,
    Star,
} from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button, buttonVariants } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { cn } from "@/lib/utils";

const PIPELINE = [
    {
        icon: Database,
        title: "Aggregate",
        description:
            "We scrape running events from multiple Polish calendars and pull them into one place, so you never have to check a dozen sites again.",
    },
    {
        icon: GitMerge,
        title: "Normalize & dedupe",
        description:
            "Voivodeships, distances and dates get cleaned into a consistent shape. The same race listed on two sources is detected and merged into one entry.",
    },
    {
        icon: Sparkles,
        title: "Enrich",
        description:
            "Existing races are filled in with missing regions, distances and map coordinates instead of being thrown away as duplicates.",
    },
    {
        icon: Search,
        title: "Search",
        description:
            "Everything lands in a fast full-text index with filters for city, region and distance, ready the moment you start typing.",
    },
];

const FEATURES = [
    {
        icon: CalendarSearch,
        title: "One running calendar",
        description:
            "Hundreds of events across Poland, from local 5Ks to full marathons, kept up to date automatically.",
    },
    {
        icon: ListChecks,
        title: "Smart filters",
        description:
            "Narrow down by city, voivodeship and distance to find exactly the race that fits your plan.",
    },
    {
        icon: MapPin,
        title: "Map view",
        description:
            "See where each start line actually is, with geocoded locations you can explore on the map.",
    },
    {
        icon: Star,
        title: "Watchlist & reviews",
        description:
            "Save races you care about and share how they went so other runners know what to expect.",
    },
    {
        icon: ShieldCheck,
        title: "Clean, deduplicated data",
        description:
            "Fuzzy matching removes duplicates and merges editions, so one race means one listing.",
    },
    {
        icon: Globe,
        title: "Open REST API",
        description:
            "The same normalized data that powers the site is available through a documented REST API.",
    },
];

function SectionHeading({ eyebrow, title }: { eyebrow: string; title: string }) {
    return (
        <div className="flex flex-col gap-2">
            <span className="text-xs font-semibold tracking-widest text-primary uppercase">
                {eyebrow}
            </span>
            <h2 className="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                {title}
            </h2>
        </div>
    );
}

export default function AboutPage() {
    return (
        <div className="flex flex-col gap-16 py-4 sm:gap-20">
            <section className="relative px-2 pt-6 text-center sm:pt-10">
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
                        About Raceboard
                    </Badge>

                    <div className="space-y-3">
                        <h1 className="text-4xl font-bold tracking-tight text-balance text-foreground sm:text-5xl">
                            Every Polish running race, in one place
                        </h1>
                        <p className="mx-auto max-w-xl text-base leading-relaxed text-pretty text-muted-foreground">
                            Raceboard gathers running events from across Poland, cleans them up and makes
                            them searchable, so you can spend less time hunting through calendars and more
                            time training.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center justify-center gap-3">
                        <Button render={<Link to="/" />} nativeButton={false}>
                            Browse races
                            <ArrowRight className="size-4" />
                        </Button>
                        <Link
                            to="/register"
                            className={cn(buttonVariants({ variant: "outline" }))}
                        >
                            Create a free account
                        </Link>
                    </div>
                </div>
            </section>

            <section className="flex flex-col gap-8">
                <SectionHeading eyebrow="The problem" title="Race info is scattered everywhere" />
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <p className="text-base leading-relaxed text-muted-foreground">
                        If you run in Poland, you know the drill. Every race calendar has its own layout,
                        its own gaps and its own way of naming things. The same event shows up on several
                        sites with slightly different dates, distances or spelling, and half of them never
                        tell you where the start line actually is.
                    </p>
                    <p className="text-base leading-relaxed text-muted-foreground">
                        Raceboard fixes that by treating messy public data as a pipeline. It collects
                        events, normalizes the details, removes duplicates and enriches what is missing.
                        The result is a single, trustworthy calendar you can actually search.
                    </p>
                </div>
            </section>

            <section className="flex flex-col gap-8">
                <SectionHeading eyebrow="How it works" title="From scattered pages to one clean feed" />
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {PIPELINE.map((step, index) => (
                        <Card key={step.title} size="sm" className="h-full">
                            <CardContent className="flex flex-col gap-3">
                                <div className="flex items-center justify-between">
                                    <span className="flex size-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                        <step.icon className="size-5" strokeWidth={1.75} />
                                    </span>
                                    <span className="text-sm font-semibold text-muted-foreground/60 tabular-nums">
                                        {String(index + 1).padStart(2, "0")}
                                    </span>
                                </div>
                                <div className="flex flex-col gap-1.5">
                                    <h3 className="text-base font-semibold text-foreground">
                                        {step.title}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-muted-foreground">
                                        {step.description}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </section>

            <section className="flex flex-col gap-8">
                <SectionHeading eyebrow="What you get" title="Built for runners, not spreadsheets" />
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {FEATURES.map((feature) => (
                        <div
                            key={feature.title}
                            className="flex flex-col gap-3 rounded-2xl border border-border bg-card p-5 ring-1 ring-foreground/5 transition-colors hover:border-primary/30"
                        >
                            <span className="flex size-10 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                <feature.icon className="size-5" strokeWidth={1.75} />
                            </span>
                            <div className="flex flex-col gap-1.5">
                                <h3 className="text-base font-semibold text-foreground">
                                    {feature.title}
                                </h3>
                                <p className="text-sm leading-relaxed text-muted-foreground">
                                    {feature.description}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>
            </section>

            <section>
                <Card className="overflow-hidden">
                    <CardContent className="flex flex-col items-center gap-5 py-10 text-center">
                        <span className="flex size-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                            <Footprints className="size-7" strokeWidth={1.75} />
                        </span>
                        <div className="flex max-w-md flex-col gap-2">
                            <h2 className="text-2xl font-bold tracking-tight text-foreground">
                                Ready to find your next race?
                            </h2>
                            <p className="text-sm leading-relaxed text-muted-foreground">
                                Search the calendar, save the races you like and never miss a start line
                                again.
                            </p>
                        </div>
                        <Separator className="max-w-xs" />
                        <div className="flex flex-wrap items-center justify-center gap-3">
                            <Button render={<Link to="/" />} nativeButton={false}>
                                Browse races
                                <ArrowRight className="size-4" />
                            </Button>
                            <Link
                                to="/register"
                                className={cn(buttonVariants({ variant: "outline" }))}
                            >
                                Create a free account
                            </Link>
                        </div>
                    </CardContent>
                </Card>
            </section>
        </div>
    );
}
