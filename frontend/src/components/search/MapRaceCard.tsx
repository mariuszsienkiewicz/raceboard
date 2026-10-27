import { ArrowRight, MapPin } from "lucide-react";
import { cn } from "@/lib/utils";
import type { MapSearchPoint } from "@/types/search";
import SearchReturnLink from "./SearchReturnLink";

interface MapRaceCardProps {
    point: MapSearchPoint;
    className?: string;
}

export default function MapRaceCard({ point, className }: MapRaceCardProps) {
    return (
        <SearchReturnLink to={`/races/${point.id}`} className={cn("block", className)}>
            <article
                className={cn(
                    "group flex flex-col gap-2 rounded-2xl border border-border bg-card p-4 text-card-foreground",
                    "shadow-md ring-1 ring-foreground/5 transition-all duration-200",
                    "hover:-translate-y-px hover:shadow-lg dark:ring-foreground/10",
                )}
            >
                <h3 className="flex items-start gap-1.5 pr-1 text-sm leading-snug font-semibold text-foreground transition-colors group-hover:text-primary">
                    <span className="min-w-0 flex-1">{point.name}</span>
                    <ArrowRight className="mt-0.5 size-3.5 shrink-0 -translate-x-1 opacity-0 transition-all duration-200 group-hover:translate-x-0 group-hover:opacity-100" />
                </h3>
                <div className="flex items-center gap-1.5 text-sm text-muted-foreground">
                    <MapPin className="size-3.5 shrink-0" />
                    <span>{point.city}</span>
                </div>
            </article>
        </SearchReturnLink>
    );
}
