import { Footprints } from "lucide-react";
import { useSearchParams } from "react-router-dom";
import Search from "@/components/search/Search";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";

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
                                Discover hundreds of running events across Poland — from local 5Ks to full
                                marathons.
                            </p>
                        </div>
                    </div>
                </section>
            )}

            <Search />
        </div>
    );
}
