import type { LucideIcon } from "lucide-react";
import { Map, Search, SearchX, Footprints } from "lucide-react";

const ICONS = {
    search: Search,
    map: Map,
    notFound: SearchX,
    footprints: Footprints,
} as const;

type EmptyStateIcon = keyof typeof ICONS;

interface EmptyStateProps {
    icon: EmptyStateIcon;
    title: string;
    description?: string;
}

export default function EmptyState({ icon, title, description }: EmptyStateProps) {
    const Icon: LucideIcon = ICONS[icon];

    return (
        <div className="flex flex-col items-center gap-3 py-16 text-center">
            <div className="flex size-12 items-center justify-center rounded-full bg-surface ring-1 ring-border">
                <Icon className="size-5 text-muted" strokeWidth={1.75} />
            </div>
            <div className="flex flex-col gap-1">
                <p className="font-semibold text-foreground">{title}</p>
                {description && <p className="text-sm text-muted">{description}</p>}
            </div>
        </div>
    );
}
